<?php

class TelegramApiParser
{
    private string $rawJsonUrl = 'https://raw.githubusercontent.com/laraxgram/telegram-api-data/main/telegram-api.json';
    private array $methods = [];
    private array $types = [];
    private ?array $typeNames = null;
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

    private function convertParameterType(string $type, bool $namespaced = true): string
    {
        $phpType = $this->convertToPhpType($type);
        $prefix = $namespaced ? 'Updates\\' : '';

        $hasUpdateClass = false;
        $parts = array_map(function (string $part) use (&$hasUpdateClass, $prefix) {
            $base = preg_replace('/(\[\])+$/', '', $part);

            if ($base !== $part) {
                if (in_array($base, ['int', 'string', 'bool', 'float', 'true'], true)) {
                    return $part;
                }

                $hasUpdateClass = true;
                return $prefix . $part;
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

        $body = $unionField
            ? $this->generateVariadicInit($className)
            : $this->generateFieldsConstant($type).$this->generateInit($type);

        return <<<PHP
<?php

namespace LaraGram\Laraquest\Updates;

use LaraGram\Laraquest\Support\UpdateObject;

/**
{$propertiesStr}
**/
class {$className} extends UpdateObject
{
{$body}}

PHP;
    }

    /**
     * Build the map of the fields that are objects of their own, so they can
     * be read as objects instead of arrays.
     */
    private function generateFieldsConstant(array $type): string
    {
        $entries = [];

        foreach ($type['fields'] as $field) {
            $mapped = $this->fieldObjectType($field['type']);

            if ($mapped !== null) {
                $entries[] = "        '{$field['name']}' => {$mapped},";
            }
        }

        if ($entries === []) {
            return '';
        }

        $entriesStr = implode("\n", $entries);

        return <<<PHP
    /**
     * The type of each field that is an object of its own.
     *
     * @var array<string, class-string|array>
     */
    protected const FIELDS = [
{$entriesStr}
    ];


PHP;
    }

    /**
     * Get the class, or list of classes, a field hydrates into.
     */
    private function fieldObjectType(string $type): ?string
    {
        $depth = 0;
        $base = trim($type);

        while (preg_match('/^Array of (.+)$/i', $base, $matches)) {
            $depth++;
            $base = trim($matches[1]);
        }

        if (! $this->isTypeName($base)) {
            return null;
        }

        $mapped = $base.'::class';

        for ($i = 0; $i < $depth; $i++) {
            $mapped = '['.$mapped.']';
        }

        return $mapped;
    }

    /**
     * Determine whether the given API type is an object described by the API.
     */
    private function isTypeName(string $type): bool
    {
        $this->typeNames ??= array_flip(array_column($this->types, 'name'));

        return isset($this->typeNames[$type]);
    }

    /**
     * Build the init() of a union type, which has no fields of its own.
     */
    private function generateVariadicInit(string $className): string
    {
        return <<<PHP
    /**
     * Build a new {$className} from the fields of the type it stands for.
     *
     * @param  mixed  ...\$fields
     * @return static
     */
    public static function init(mixed ...\$fields): static
    {
        return static::make(\$fields);
    }

PHP;
    }

    /**
     * Build the init() of a type, with one parameter per field so an editor
     * can list and describe them.
     */
    private function generateInit(array $type): string
    {
        $className = $type['name'];
        $parameters = [];
        $docs = [];

        foreach ($type['fields'] as $field) {
            $native = $this->initParameterType($field['type']);
            $docType = $this->initDocType($field['type']);

            $parameters[] = '        '.($native ? $native.' ' : '')."\${$field['name']} = null,";
            $docs[] = "     * @param  {$docType}  \${$field['name']}  {$this->escapeDescription($field['description'])}";
        }

        if ($parameters === []) {
            return $this->generateVariadicInit($className);
        }

        $parametersStr = implode("\n", $parameters);
        $docsStr = implode("\n", $docs);

        return <<<PHP
    /**
     * Build a new {$className}.
     *
{$docsStr}
     * @return static
     */
    public static function init(
{$parametersStr}
    ): static {
        return static::make(get_defined_vars());
    }

PHP;
    }

    /**
     * Describe an init() parameter for the editor: the type the API documents,
     * plus the shapes the parameter also accepts.
     */
    private function initDocType(string $type): string
    {
        $parts = [];

        foreach (explode('|', $this->convertToPhpType($type)) as $part) {
            $base = preg_replace('/(\[\])+$/', '', $part);

            if (in_array($base, ['int', 'string', 'bool', 'float', 'true'], true)) {
                $parts[] = $base === 'true' ? 'bool' : $part;

                if ($base === 'int') {
                    $parts[] = 'string';
                }

                continue;
            }

            $parts[] = $part;
            $parts[] = 'array';
        }

        $parts[] = 'null';

        return implode('|', array_unique($parts));
    }

    /**
     * Get the native type of an init() parameter, or null when the field holds
     * an object and should stay open to whatever the caller has at hand.
     */
    private function initParameterType(string $type): ?string
    {
        $native = [];

        foreach (explode('|', $this->convertToPhpType($type)) as $part) {
            if (str_ends_with($part, '[]')) {
                $native[] = 'array';

                continue;
            }

            match ($part) {
                'int' => array_push($native, 'int', 'string'),
                'float' => array_push($native, 'float', 'int'),
                'string' => $native[] = 'string',
                'bool', 'true' => $native[] = 'bool',
                default => $native[] = '?',
            };
        }

        if (in_array('?', $native, true)) {
            return null;
        }

        $native = array_values(array_unique($native));
        $native[] = 'null';

        return implode('|', $native);
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
        $returnDoc    = $this->returnDoc($method);

        return <<<PHP
    /**
     * {$description}{$docParamsStr}
     * @return {$returnDoc}
     *
     * @throws \LaraGram\Laraquest\Exceptions\TelegramApiException
     */
    public function {$methodName}({$paramsStr}): Response
    {
        return \$this->endpoint('{$methodName}', get_defined_vars());
    }
PHP;
    }

    /**
     * Describe the response of a method as an array shape, so an editor knows
     * what comes back and what its "result" holds.
     */
    private function returnDoc(array $method): string
    {
        $returns = $this->extractReturnType(
            $method['description'], array_column($this->types, 'name')
        );

        $types = $returns === null ? ['mixed'] : array_map(
            fn (string $type) => $this->returnResultType($type), explode('|', $returns)
        );

        $result = implode('|', $types);
        $shape = "array{ok: bool, result: {$result}, description?: string, error_code?: int, parameters?: array}";

        // The response reads as the object the call returned, as the array
        // Telegram sent, and as the response object itself - so every one of
        // them is offered to the editor.
        $objects = array_values(array_filter(
            $types, fn (string $type) => str_starts_with($type, 'Updates\\')
        ));

        return implode('|', array_merge(['Response'], $objects, [$shape]));
    }

    /**
     * Map one documented return type onto the type of the "result" field.
     */
    private function returnResultType(string $type): string
    {
        if (preg_match('/^Array of (.+)$/i', trim($type), $matches)) {
            return $this->returnResultType(trim($matches[1])).'[]';
        }

        return match (trim($type)) {
            'True' => 'true',
            'Integer' => 'int',
            'String' => 'string',
            'Boolean' => 'bool',
            'Float' => 'float',
            default => 'Updates\\'.trim($type),
        };
    }

    /**
     * Write the raw API description as a PHP array, so it can be read at runtime
     * without network access (e.g. to build tool schemas).
     *
     * @throws Exception
     */
    private function generateSchemaFile(array $data): void
    {
        $schemaDir = $this->baseDir . '/src/Schema';
        if (!is_dir($schemaDir)) mkdir($schemaDir, 0755, true);

        $typeNames = array_column($this->types, 'name');

        $methods = [];
        foreach ($this->methods as $method) {
            $methods[$method['name']] = [
                'description' => $method['description'],
                'returns'     => $this->extractReturnType($method['description'], $typeNames),
                'parameters'  => array_map(fn(array $param) => [
                    'name'        => $param['name'],
                    'type'        => $param['type'],
                    'required'    => !empty($param['required']),
                    'description' => $param['description'],
                ], array_values($method['parameters'] ?? [])),
            ];
        }

        $types = [];
        foreach ($this->types as $type) {
            $types[$type['name']] = [
                'description' => $type['description'],
                'fields'      => array_map(fn(array $field) => [
                    'name'        => $field['name'],
                    'type'        => $field['type'],
                    'required'    => $field['name'] === '__union__' || !str_starts_with(trim($field['description']), 'Optional.'),
                    'description' => $field['description'],
                ], array_values($type['fields'] ?? [])),
            ];
        }

        $schema = [
            'version'    => $data['version'] ?? null,
            'scraped_at' => $data['scraped_at'] ?? null,
            'methods'    => $methods,
            'types'      => $types,
        ];

        $content = "<?php\n\nreturn " . var_export($schema, true) . ";\n";

        if (!file_put_contents($schemaDir . '/api.php', $content)) {
            throw new Exception("Error writing Schema/api.php");
        }

        $returns = array_map(fn (array $method) => $method['returns'], $methods);

        $content = "<?php\n\n// What every Bot API method returns, as the API describes it.\n\nreturn "
            . var_export($returns, true) . ";\n";

        if (!file_put_contents($schemaDir . '/returns.php', $content)) {
            throw new Exception("Error writing Schema/returns.php");
        }
    }

    /**
     * Extract the return type from a method description, e.g. "Message",
     * "Array of Update" or "Message|True".
     */
    private function extractReturnType(string $description, array $typeNames): ?string
    {
        $known = array_flip(array_merge($typeNames, ['True', 'Integer', 'String', 'Boolean']));
        $found = [];

        foreach (preg_split('/(?<=[.!?])\s+/', $description) as $sentence) {
            if (!preg_match('/\breturn(s|ed)?\b/i', $sentence)) continue;

            preg_match_all('/\b(Array of )?([A-Z][A-Za-z]+)\b/', $sentence, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                if (!isset($known[$match[2]])) continue;

                $found[] = $match[1] !== '' ? 'Array of ' . $match[2] : $match[2];
            }
        }

        $found = array_values(array_unique($found));

        return $found === [] ? null : implode('|', $found);
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

        echo "Generating API schema..." . PHP_EOL;
        $this->generateSchemaFile($data);

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
