<?php

/**
 * Telegram Bot API Parser
 * This program fetches and parses Telegram API from the official website
 */
class TelegramApiParser
{
    private string $apiUrl = 'https://core.telegram.org/bots/api';
    private array $methods = [];
    private array $types = [];
    private string $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * Fetch API page content
     *
     * @throws Exception
     */
    private function fetchApiPage(): string
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$html) {
            throw new Exception("Error fetching API page. HTTP Code: $httpCode");
        }

        return $html;
    }

    /**
     * Parse methods
     */
    private function parseMethods(string $html): void
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Find all h4 headings that are method names
        $methodHeadings = $xpath->query("//h4[not(contains(@class, 'no-link'))]");

        foreach ($methodHeadings as $heading) {
            $methodName = trim($heading->textContent);

            // Check if this is a valid method (starts with lowercase letter)
            if (!preg_match('/^[a-z]/', $methodName) || strlen($methodName) > 50) {
                continue;
            }

            // Find method description
            $description = $this->getMethodDescription($heading, $xpath);

            // Find parameters table
            $parameters = $this->getMethodParameters($heading, $xpath);

            $this->methods[$methodName] = [
                'name' => $methodName,
                'description' => $description,
                'parameters' => $parameters
            ];
        }

    }

    /**
     * Get method description
     */
    private function getMethodDescription(DOMElement $heading, DOMXPath $xpath): string
    {
        $description = '';
        $node = $heading->nextSibling;

        // Read paragraphs after heading until reaching table or next heading
        while ($node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                if ($node->nodeName === 'p') {
                    $text = trim($node->textContent);
                    if ($text) {
                        $description .= $text . ' ';
                    }
                } elseif (in_array($node->nodeName, ['h3', 'h4', 'table'])) {
                    break;
                }
            }
            $node = $node->nextSibling;
        }

        return trim($description);
    }

    /**
     * Get method parameters
     */
    private function getMethodParameters(DOMElement $heading, DOMXPath $xpath): array
    {
        $parameters = [];
        $node = $heading->nextSibling;

        // Find parameters table
        while ($node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                if ($node->nodeName === 'table') {
                    $rows = $xpath->query('.//tr', $node);

                    foreach ($rows as $index => $row) {
                        // Skip table header
                        if ($index === 0) continue;

                        $cells = $xpath->query('.//td', $row);
                        if ($cells->length >= 3) {
                            $name = trim($cells->item(0)->textContent);
                            $type = trim($cells->item(1)->textContent);
                            $required = trim($cells->item(2)->textContent);
                            $description = $cells->length > 3 ? trim($cells->item(3)->textContent) : '';

                            if ($name && $name !== 'Field' && $name !== 'Parameter') {
                                $parameters[] = [
                                    'name' => $name,
                                    'type' => $type,
                                    'required' => strtolower($required) === 'yes' || strtolower($required) === 'true',
                                    'description' => $description
                                ];
                            }
                        }
                    }
                    break;
                } elseif (in_array($node->nodeName, ['h3', 'h4'])) {
                    break;
                }
            }
            $node = $node->nextSibling;
        }

        // Sort parameters: required first, then optional
        usort($parameters, function($a, $b) {
            if ($a['required'] === $b['required']) {
                return 0;
            }
            return $a['required'] ? -1 : 1;
        });

        return $parameters;
    }

    /**
     * Parse types
     */
    private function parseTypes(string $html): void
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Find all h4 headings that are type names
        $typeHeadings = $xpath->query("//h4[not(contains(@class, 'no-link'))]");

        foreach ($typeHeadings as $heading) {
            $typeName = trim($heading->textContent);

            // Check if this is a valid type (starts with uppercase letter)
            if (!preg_match('/^[A-Z]/', $typeName) || strlen($typeName) > 50) {
                continue;
            }

            // Skip methods that start with lowercase
            if (preg_match('/^[a-z]/', $typeName)) {
                continue;
            }

            // Skip 'Determining list of commands'
            if ($typeName === 'Determining list of commands') {
                continue;
            }

            // Find type description
            $description = $this->getMethodDescription($heading, $xpath);

            // Find fields
            $fields = $this->getTypeFields($heading, $xpath);

            $this->types[$typeName] = [
                'name' => $typeName,
                'description' => $description,
                'fields' => $fields
            ];
        }

    }

    /**
     * Get type fields
     */
    private function getTypeFields(DOMElement $heading, DOMXPath $xpath): array
    {
        $fields = [];
        $node = $heading->nextSibling;

        // Check if this is a union type by looking for "can be one of" pattern
        $isUnionType = false;
        $unionSubtypes = [];
        $tempNode = $node;

        while ($tempNode && !$isUnionType) {
            if ($tempNode->nodeType === XML_ELEMENT_NODE) {
                if ($tempNode->nodeName === 'p') {
                    $text = trim($tempNode->textContent);
                    // Check for union type patterns
                    if (preg_match('/(?:can be one of|currently,?\s+it can be one of|should be one of|following.*(?:types|scopes) (?:are|is) supported|the following.*types)/i', $text)) {
                        $isUnionType = true;

                        // Look for list items after this paragraph
                        $listNode = $tempNode->nextSibling;
                        while ($listNode) {
                            if ($listNode->nodeType === XML_ELEMENT_NODE && $listNode->nodeName === 'ul') {
                                $items = $xpath->query('.//li', $listNode);
                                foreach ($items as $item) {
                                    $subtype = trim($item->textContent);
                                    // Only add if it looks like a valid type name (starts with uppercase)
                                    if (preg_match('/^[A-Z][a-zA-Z0-9]+/', $subtype, $matches)) {
                                        $unionSubtypes[] = $matches[0];
                                    }
                                }
                                break;
                            }
                            $listNode = $listNode->nextSibling;
                        }
                        break;
                    }
                } elseif (in_array($tempNode->nodeName, ['h3', 'h4', 'table'])) {
                    break;
                }
            }
            $tempNode = $tempNode->nextSibling;
        }

        // If it's a union type, store the subtypes in a special field
        if ($isUnionType && !empty($unionSubtypes)) {
            $fields[] = [
                'name' => '__union__',
                'type' => implode('|', $unionSubtypes),
                'description' => ''
            ];
            return $fields;
        }

        // Find fields table (for regular types)
        while ($node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                if ($node->nodeName === 'table') {
                    $rows = $xpath->query('.//tr', $node);

                    foreach ($rows as $index => $row) {
                        // Skip table header
                        if ($index === 0) continue;

                        $cells = $xpath->query('.//td', $row);
                        if ($cells->length >= 2) {
                            $name = trim($cells->item(0)->textContent);
                            $type = trim($cells->item(1)->textContent);
                            $description = $cells->length > 2 ? trim($cells->item(2)->textContent) : '';

                            if ($name && $name !== 'Field' && $name !== 'Parameter') {
                                $fields[] = [
                                    'name' => $name,
                                    'type' => $type,
                                    'description' => $description
                                ];
                            }
                        }
                    }
                    break;
                } elseif (in_array($node->nodeName, ['h3', 'h4'])) {
                    break;
                }
            }
            $node = $node->nextSibling;
        }

        return $fields;
    }

    /**
     * Convert Telegram type to PHP type
     */
    private function convertToPhpType(string $type): string
    {
        // Handle array types
        if (preg_match('/^Array of (.+)$/i', $type, $matches)) {
            $innerType = trim($matches[1]);
            return $this->convertToPhpType($innerType) . '[]';
        }

        // Handle "or" types
        if (stripos($type, ' or ') !== false) {
            $parts = array_map('trim', explode(' or ', $type));
            return implode('|', array_map([$this, 'convertToPhpType'], $parts));
        }

        // Basic type conversions
        $typeMap = [
            'Integer' => 'int',
            'String' => 'string',
            'Boolean' => 'bool',
            'Float' => 'float',
            'True' => 'true',
        ];

        return $typeMap[$type] ?? $type;
    }

    /**
     * Get subclasses for a type (union types)
     */
    private function getSubclasses(string $typeName): array
    {
        $subclasses = [];

        foreach ($this->types as $type) {
            if (!empty($type['fields'])) {
                // Check if this type has a 'type' field that references the parent
                foreach ($type['fields'] as $field) {
                    if ($field['name'] === 'type' && stripos($field['description'], $typeName) !== false) {
                        $subclasses[] = $type['name'];
                        break;
                    }
                }
            }
        }

        return $subclasses;
    }

    /**
     * Escape description for PHPDoc
     */
    private function escapeDescription(string $description): string
    {
        // Remove multiple spaces and newlines
        $description = preg_replace('/\s+/', ' ', $description);
        $description = trim($description);

        // Escape special characters
        $description = str_replace(['*/', '/*'], ['* /', '/ *'], $description);

        return $description;
    }

    /**
     * Generate type class files
     */
    private function generateTypeFiles(): void
    {
        $typesDir = $this->baseDir . '/src/Updates';
        if (!is_dir($typesDir)) {
            mkdir($typesDir, 0755, true);
        }

        $generatedCount = 0;
        foreach ($this->types as $type) {
            // Skip invalid type names (dates, sentences, etc.)
            if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $type['name'])) {
                continue;
            }

            $className = $type['name'];
            $filename = $typesDir . '/' . $className . '.php';

            $content = $this->generateTypeClass($type);

            file_put_contents($filename, $content);
        }
    }

    /**
     * Generate type class content
     */
    private function generateTypeClass(array $type): string
    {
        $className = $type['name'];
        $this->escapeDescription($type['description']);

        // Check if this is a union type (has __union__ field)
        $unionField = null;
        foreach ($type['fields'] as $field) {
            if ($field['name'] === '__union__') {
                $unionField = $field;
                break;
            }
        }

        if ($unionField) {
            // This is a union type parent, create union in docblock
            $unionTypes = $unionField['type'];
            $propertiesStr = " * @mixin {$unionTypes}";
        } else {
            // Regular type with properties
            $properties = [];
            foreach ($type['fields'] as $field) {
                $fieldName = $field['name'];
                $fieldType = $this->convertToPhpType($field['type']);
                $fieldDesc = $this->escapeDescription($field['description']);

                $properties[] = " * @property {$fieldType} \${$fieldName}" . ($fieldDesc ? " {$fieldDesc}" : '');
            }

            $propertiesStr = implode("\n", $properties);
        }

        $content = <<<PHP
<?php

namespace LaraGram\Laraquest\Updates;

/**
{$propertiesStr}
**/
class {$className} { }

PHP;

        return $content;
    }

    /**
     * Generate API methods trait
     * @throws Exception
     */
    private function generateApiMethodsTrait(): void
    {
        $srcDir = $this->baseDir . '/src';
        if (!is_dir($srcDir)) {
            mkdir($srcDir, 0755, true);
        }

        $filename = $srcDir . '/APIMethods.php';

        $methods = [];
        foreach ($this->methods as $method) {
            $methods[] = $this->generateMethod($method);
        }

        $methodsStr = implode("\n\n", $methods);

        $content = <<<PHP
<?php

namespace LaraGram\Laraquest;

trait APIMethods
{
{$methodsStr}
}

PHP;

        if (!file_put_contents($filename, $content)) {
            throw new Exception("Error generating APIMethods trait");
        }
    }

    /**
     * Convert parameter type for docblock
     */
    private function convertParameterType(string $type): string
    {
        // First convert using existing type conversion
        $phpType = $this->convertToPhpType($type);

        // Handle int types - add string union
        if ($phpType === 'int') {
            return 'int|string';
        }

        // Handle float types - add string union
        if ($phpType === 'float') {
            return 'float|string';
        }

        // Check if it's a basic scalar type
        $basicTypes = ['string', 'bool', 'true'];
        foreach ($basicTypes as $basicType) {
            if ($phpType === $basicType) {
                return $phpType;
            }
        }

        // Everything else (classes, unions, arrays, etc.) should be just array
        return 'array';
    }

    /**
     * Generate single method
     */
    private function generateMethod(array $method): string
    {
        $methodName = $method['name'];
        $description = $this->escapeDescription($method['description']);

        // Build parameters
        $params = [];
        $docParams = [];

        // Separate required and optional while preserving original order
        $all = $method['parameters'] ?? [];
        $requiredParams = [];
        $optionalParams = [];
        foreach ($all as $p) {
            if (!empty($p['required'])) {
                $requiredParams[] = $p;
            } else {
                $optionalParams[] = $p;
            }
        }

        // Prioritize certain optional params in this order if they exist
        $priority = ['chat_id', 'message_id', 'parse_mode', 'reply_markup'];
        $orderedOptional = [];
        // pull priority params first
        foreach ($priority as $pr) {
            foreach ($optionalParams as $k => $op) {
                if ($op['name'] === $pr) {
                    $orderedOptional[] = $op;
                    unset($optionalParams[$k]);
                    break;
                }
            }
        }
        // append remaining optional params in their original order
        foreach ($optionalParams as $op) {
            $orderedOptional[] = $op;
        }

        $ordered = array_merge($requiredParams, $orderedOptional);

        foreach ($ordered as $param) {
            $paramName = $param['name'];
            $paramType = $this->convertParameterType($param['type']);
            $paramDesc = $this->escapeDescription($param['description']);

            if (!empty($param['required'])) {
                $params[] = "\${$paramName}";
            } else {
                $params[] = "\${$paramName} = null";
            }

            $docParams[] = "     * @param {$paramType} \${$paramName} {$paramDesc}";
        }

        $paramsStr = implode(', ', $params);
        $docParamsStr = empty($docParams) ? '' : "\n" . implode("\n", $docParams);

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
     * Run parser
     */
    public function run(): void
    {
        try {
            $startTime = microtime(true);

            // Fetch API content
            $html = $this->fetchApiPage();

            // Parse methods
            $this->parseMethods($html);

            // Parse types
            $this->parseTypes($html);

            // Generate type files
            $this->generateTypeFiles();

            // Generate API methods trait
            $this->generateApiMethodsTrait();

        } catch (Exception $e) {
            exit(1);
        }
    }
}

if (php_sapi_name() === 'cli') {
    $baseDir = __DIR__ . '/..';
    $parser = new TelegramApiParser($baseDir);
    $parser->run();
} else {
    exit(1);
}
