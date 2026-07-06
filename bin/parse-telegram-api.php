<?php

class TelegramApiParser
{
    private string $rawJsonUrl = 'https://raw.githubusercontent.com/laraxgram/telegram-api-data/main/telegram-api.json';
    private array $methods = [];
    private array $types = [];
    private string $baseDir;

    private array $textFields = ['text', 'caption'];
    private array $mediaFields = ['photo', 'audio', 'document', 'video', 'animation', 'voice', 'video_note', 'sticker', 'media', 'thumbnail', 'thumb'];
    private array $messageIdFields = ['message_id', 'message_ids'];

    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function setRawJsonUrl(string $url): void
    {
        $this->rawJsonUrl = $url;
    }

    /**
     * @throws Exception
     */
    private function fetchApiJson(): array
    {
        $ch = curl_init($this->rawJsonUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

        $json     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200 || !$json) {
            throw new Exception("Failed fetching JSON. HTTP $httpCode\nURL: {$this->rawJsonUrl}");
        }

        $data = json_decode($json, true);
        if (!$data || !isset($data['methods'], $data['types'])) {
            throw new Exception("Invalid JSON from GitHub raw content.");
        }

        return $data;
    }

    private function convertToPhpType(string $type): string
    {
        if (preg_match('/^Array of (.+)$/i', $type, $matches)) {
            return $this->convertToPhpType(trim($matches[1])) . '[]';
        }

        if (stripos($type, ' or ') !== false) {
            $parts = array_map('trim', explode(' or ', $type));
            return implode('|', array_map([$this, 'convertToPhpType'], $parts));
        }

        return ['Integer' => 'int', 'String' => 'string', 'Boolean' => 'bool', 'Float' => 'float', 'True' => 'true'][$type] ?? $type;
    }

    private function convertParameterType(string $type): string
    {
        $phpType = $this->convertToPhpType($type);

        $hasUpdateClass = false;
        $parts = array_map(function (string $part) use (&$hasUpdateClass) {
            $base = preg_replace('/(\[\])+$/', '', $part);

            if ($base !== $part) {
                if (in_array($base, ['int', 'string', 'bool', 'float', 'true'], true)) {
                    return $part;
                }

                $hasUpdateClass = true;
                return 'Updates\\' . $part;
            }

            return in_array($part, ['int', 'string', 'bool', 'float', 'true'], true) ? $part : 'array';
        }, explode('|', $phpType));

        if (in_array('int', $parts, true) || in_array('float', $parts, true)) {
            $parts[] = 'string';
        }

        if ($hasUpdateClass) {
            $parts[] = 'array';
            $parts[] = 'string';
        }

        return implode('|', array_unique($parts));
    }

    private function paramRank(string $name, bool $isEdit, bool $optionalGroup): int
    {
        if ($isEdit) {
            if (in_array($name, $this->textFields, true)) return 0;
            if ($name === 'chat_id') return 1;
            if ($optionalGroup && in_array($name, $this->messageIdFields, true)) return 2;
            if (in_array($name, $this->mediaFields, true)) return 3;
        } else {
            if ($name === 'chat_id') return 0;
            if (in_array($name, $this->textFields, true)) return 1;
            if (in_array($name, $this->mediaFields, true)) return 2;
            if ($optionalGroup && in_array($name, $this->messageIdFields, true)) return 3;
        }

        if ($optionalGroup) {
            if ($name === 'parse_mode') return 10;
            if ($name === 'reply_markup') return 11;
        }

        return 100;
    }

    private function orderParams(array $params, bool $isEdit, bool $optionalGroup): array
    {
        $ranked = [];
        foreach (array_values($params) as $idx => $param) {
            $ranked[] = ['param' => $param, 'rank' => $this->paramRank($param['name'], $isEdit, $optionalGroup), 'idx' => $idx];
        }

        usort($ranked, fn($a, $b) => $a['rank'] <=> $b['rank'] ?: $a['idx'] <=> $b['idx']);

        return array_column($ranked, 'param');
    }

    private function escapeDescription(string $description): string
    {
        return str_replace(['*/', '/*'], ['* /', '/ *'], trim(preg_replace('/\s+/', ' ', $description)));
    }

    private function generateTypeFiles(): void
    {
        $typesDir = $this->baseDir . '/src/Updates';
        if (!is_dir($typesDir)) {
            mkdir($typesDir, 0755, true);
        }

        foreach ($this->types as $type) {
            if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $type['name'])) continue;
            file_put_contents($typesDir . '/' . $type['name'] . '.php', $this->generateTypeClass($type));
        }
    }

    private function generateTypeClass(array $type): string
    {
        $className  = $type['name'];
        $unionField = null;

        foreach ($type['fields'] as $field) {
            if ($field['name'] === '__union__') { $unionField = $field; break; }
        }

        if ($unionField) {
            $propertiesStr = " * @mixin {$unionField['type']}";
        } else {
            $lines = [];
            foreach ($type['fields'] as $field) {
                $desc   = $this->escapeDescription($field['description']);
                $lines[] = " * @property {$this->convertToPhpType($field['type'])} \${$field['name']}" . ($desc ? " $desc" : '');
            }
            $propertiesStr = implode("\n", $lines);
        }

        return <<<PHP
<?php

namespace LaraGram\Laraquest\Updates;

use LaraGram\Laraquest\Support\UpdateObject;

/**
{$propertiesStr}
**/
class {$className} extends UpdateObject { }

PHP;
    }

    /**
     * @throws Exception
     */
    private function generateApiMethodsTrait(): void
    {
        $srcDir = $this->baseDir . '/src';
        if (!is_dir($srcDir)) mkdir($srcDir, 0755, true);

        $methods = array_map([$this, 'generateMethod'], array_values($this->methods));

        $content = <<<PHP
<?php

namespace LaraGram\Laraquest;

use LaraGram\Laraquest\Updates;

trait APIMethods
{
PHP . "\n" . implode("\n\n", $methods) . "\n}\n";

        if (!file_put_contents($srcDir . '/APIMethods.php', $content)) {
            throw new Exception("Error writing APIMethods.php");
        }
    }

    private function generateMethod(array $method): string
    {
        $methodName  = $method['name'];
        $description = $this->escapeDescription($method['description']);

        $isEdit   = str_starts_with($methodName, 'edit');
        $required = array_filter($method['parameters'] ?? [], fn($p) => !empty($p['required']));
        $optional = array_filter($method['parameters'] ?? [], fn($p) =>  empty($p['required']));

        $ordered   = array_merge(
            $this->orderParams($required, $isEdit, false),
            $this->orderParams($optional, $isEdit, true)
        );
        $params    = [];
        $docParams = [];

        foreach ($ordered as $param) {
            $params[]    = !empty($param['required']) ? "\${$param['name']}" : "\${$param['name']} = null";
            $docParams[] = "     * @param {$this->convertParameterType($param['type'])} \${$param['name']} {$this->escapeDescription($param['description'])}";
        }

        $paramsStr    = implode(', ', $params);
        $docParamsStr = $docParams ? "\n" . implode("\n", $docParams) : '';

        return <<<PHP
    /**
     * {$description}{$docParamsStr}
     */
    public function {$methodName}({$paramsStr})
    {
        return \$this->endpoint('{$methodName}', get_defined_vars());
    }
PHP;
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        echo "Fetching JSON from GitHub..." . PHP_EOL;
        $data = $this->fetchApiJson();

        $this->methods = $data['methods'];
        $this->types   = $data['types'];

        echo "Generating type files..." . PHP_EOL;
        $this->generateTypeFiles();

        echo "Generating APIMethods trait..." . PHP_EOL;
        $this->generateApiMethodsTrait();

        echo "Done." . PHP_EOL;
    }
}

if (php_sapi_name() !== 'cli') exit(1);

$baseDir = __DIR__ . '/..';
$rawUrl  = null;

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--raw-url=')) {
        $rawUrl = substr($arg, strlen('--raw-url='));
    }
}

try {
    $parser = new TelegramApiParser($baseDir);
    if ($rawUrl) $parser->setRawJsonUrl($rawUrl);
    $parser->run();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
