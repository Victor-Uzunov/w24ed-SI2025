<?php

namespace App\Core;

use App\Exception\ValidationException;

class Validator
{
    public static function validate(array $data, array $rules): void
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $error = self::validateRule($field, $data[$field] ?? null, $rule);
                if ($error) {
                    $errors[] = $error;
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    private static function validateRule(string $field, $value, string $rule): ?string
    {
        [$ruleName, $ruleValue] = array_pad(explode(':', $rule, 2), 2, null);

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0' && $value !== 0) {
                    return "Полето '$field' е задължително";
                }
                break;

            case 'min':
                if ($value < $ruleValue) {
                    return "Полето '$field' трябва да е поне $ruleValue";
                }
                break;

            case 'max':
                if ($value > $ruleValue) {
                    return "Полето '$field' трябва да е най-много $ruleValue";
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if (!in_array($value, $allowedValues)) {
                    return "Полето '$field' трябва да е една от следните стойности: " . implode(', ', $allowedValues);
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    return "Полето '$field' трябва да е число";
                }
                break;

            case 'string':
                if (!is_string($value)) {
                    return "Полето '$field' трябва да е текст";
                }
                break;
        }

        return null;
    }
} 