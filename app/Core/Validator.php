<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Validator
{
    private array $errors = [];

    public function __construct(private array $data, private array $rules)
    {
        $this->run();
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

        $isEmpty = $value === null || $value === '';

        switch ($name) {
            case 'required':
                if ($isEmpty) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
                }
                break;

            case 'email':
                if (!$isEmpty && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Enter a valid email address.');
                }
                break;

            case 'min':
                if (!$isEmpty && is_string($value) && mb_strlen($value) < (int) $param) {
                    $this->addError($field, ucfirst($field) . " must be at least {$param} characters.");
                } elseif (!$isEmpty && is_numeric($value) && (float) $value < (float) $param) {
                    $this->addError($field, ucfirst($field) . " must be at least {$param}.");
                }
                break;

            case 'max':
                if (!$isEmpty && is_string($value) && mb_strlen($value) > (int) $param) {
                    $this->addError($field, ucfirst($field) . " must not exceed {$param} characters.");
                } elseif (!$isEmpty && is_numeric($value) && (float) $value > (float) $param) {
                    $this->addError($field, ucfirst($field) . " must not exceed {$param}.");
                }
                break;

            case 'numeric':
                if (!$isEmpty && !is_numeric($value)) {
                    $this->addError($field, ucfirst($field) . ' must be a number.');
                }
                break;

            case 'in':
                $options = explode(',', (string) $param);
                if (!$isEmpty && !in_array((string) $value, $options, true)) {
                    $this->addError($field, 'Selected ' . str_replace('_', ' ', $field) . ' is invalid.');
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (($this->data[$confirmField] ?? null) !== $value) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' confirmation does not match.');
                }
                break;

            case 'unique':
                if (!$isEmpty) {
                    [$table, $column] = array_pad(explode(',', (string) $param), 2, 'id');
                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                        throw new \InvalidArgumentException('Invalid unique rule identifier.');
                    }
                    $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
                    if (self::tableHasColumn($table, 'deleted_at')) {
                        $sql .= ' AND deleted_at IS NULL';
                    }
                    $stmt = Database::connection()->prepare($sql);
                    $stmt->execute(['value' => $value]);
                    if ((int) $stmt->fetchColumn() > 0) {
                        $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is already in use.');
                    }
                }
                break;

            case 'date':
                if (!$isEmpty && \DateTime::createFromFormat('Y-m-d', (string) $value) === false) {
                    $this->addError($field, ucfirst($field) . ' must be a valid date (YYYY-MM-DD).');
                }
                break;

            case 'alpha_dash':
                if (!$isEmpty && !preg_match('/^[A-Za-z0-9_-]+$/', (string) $value)) {
                    $this->addError($field, ucfirst($field) . ' may only contain letters, numbers, dashes and underscores.');
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Not every table has soft deletes (e.g. lookup tables like industries,
     * services, skills), so the `unique` rule checks for the column instead
     * of assuming it exists. Result is cached per table for the request.
     */
    private static function tableHasColumn(string $table, string $column): bool
    {
        static $cache = [];

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            return false;
        }

        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
        );
        $stmt->execute(['table' => $table, 'column' => $column]);

        return $cache[$key] = ((int) $stmt->fetchColumn() > 0);
    }
}
