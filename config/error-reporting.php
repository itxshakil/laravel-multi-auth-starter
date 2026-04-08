<?php

$recipients = explode(',', (string)env('ERROR_REPORTING_RECIPIENTS', ''))
        |> (static fn($x) => array_map(static fn(string $recipient): string => trim($recipient), $x,))
        |> array_filter(...)
        |> array_values(...);

$sensitiveFields = explode(',', (string)env('ERROR_REPORTING_SENSITIVE_FIELDS', 'password,password_confirmation,current_password,token'))
        |> (static fn($x) => array_map(static fn(string $field): string => trim($field), $x,))
        |> array_filter(...)
        |> array_values(...);

return [
    'enabled' => env('ERROR_REPORTING_ENABLED', in_array((string) env('APP_ENV'), ['production', 'staging'], true)),
    'recipients' => $recipients,
    'throttle_seconds' => (int) env('ERROR_REPORTING_THROTTLE_SECONDS', 300),
    'sensitive_fields' => $sensitiveFields,
];
