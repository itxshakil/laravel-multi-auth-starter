<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Exception Report</title>
</head>
<body>
    <h1>Exception Report</h1>

    <p><strong>Exception:</strong> {{ $data['exception'] ?? 'Throwable' }}</p>
    <p><strong>Message:</strong> {{ $data['message'] ?? 'No message provided.' }}</p>
    <p><strong>Environment:</strong> {{ $data['environment'] ?? 'unknown' }}</p>
    <p><strong>File:</strong> {{ $data['file'] ?? 'n/a' }}</p>
    <p><strong>Line:</strong> {{ $data['line'] ?? 'n/a' }}</p>
    <p><strong>Reported At:</strong> {{ $data['reported_at'] ?? now()->toDateTimeString() }}</p>

    <h2>Request</h2>
    <p><strong>Request ID:</strong> {{ $data['request_id'] ?? 'n/a' }}</p>
    <p><strong>URL:</strong> {{ $data['url'] ?? 'n/a' }}</p>
    <p><strong>Method:</strong> {{ $data['method'] ?? 'n/a' }}</p>
    <p><strong>User ID:</strong> {{ $data['user_id'] ?? 'guest' }}</p>
    <p><strong>IP:</strong> {{ $data['ip'] ?? 'n/a' }}</p>
    <p><strong>User Agent:</strong> {{ $data['user_agent'] ?? 'n/a' }}</p>

    @if (! empty($data['public_message']))
        <p><strong>Public Message:</strong> {{ $data['public_message'] }}</p>
    @endif

    <h2>Exception Context</h2>
    <pre>{{ json_encode($data['exception_context'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

    <h2>Query</h2>
    <pre>{{ json_encode($data['query'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

    <h2>Payload</h2>
    <pre>{{ json_encode($data['payload'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

    <h2>Trace</h2>
    <pre>{{ $data['trace'] ?? 'n/a' }}</pre>
</body>
</html>
