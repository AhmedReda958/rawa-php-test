<?php

declare(strict_types=1);

function validateTask(array $input): array
{
    $values = [];
    $errors = [];

    foreach (['title', 'description', 'priority', 'status', 'due_date'] as $field) {
        $value = $input[$field] ?? '';
        $validText = is_string($value) && mb_check_encoding($value, 'UTF-8') && !str_contains($value, "\0");
        $values[$field] = $validText ? trim($value) : '';

        if (!$validText) {
            $errors[$field] = 'Please enter a valid value.';
        }
    }

    if ($values['title'] === '') {
        $errors['title'] = 'Title is required.';
    } elseif (mb_strlen($values['title'], 'UTF-8') > 255) {
        $errors['title'] = 'Title must be 255 characters or fewer.';
    }

    if (mb_strlen($values['description'], 'UTF-8') > 5000) {
        $errors['description'] = 'Description must be 5,000 characters or fewer.';
    }

    if (!in_array($values['priority'], ['low', 'medium', 'high'], true)) {
        $errors['priority'] = 'Choose a valid priority.';
    }

    if (!in_array($values['status'], ['pending', 'in_progress', 'completed'], true)) {
        $errors['status'] = 'Choose a valid status.';
    }

    if ($values['due_date'] !== '') {
        $date = preg_match('/^[1-9][0-9]{3}-[0-9]{2}-[0-9]{2}$/D', $values['due_date'])
            ? DateTimeImmutable::createFromFormat('!Y-m-d', $values['due_date'])
            : false;

        // Round-trip validation rejects dates such as February 30 and MySQL's unsupported years.
        if (!$date || $date->format('Y-m-d') !== $values['due_date'] || $date->format('Y') < 1000) {
            $errors['due_date'] = 'Enter a valid date between years 1000 and 9999.';
        }
    }

    $values['due_date'] = $values['due_date'] === '' ? null : $values['due_date'];

    return ['values' => $values, 'errors' => $errors];
}
