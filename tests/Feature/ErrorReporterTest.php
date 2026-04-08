<?php

declare(strict_types=1);

use App\Exceptions\ContextualException;
use App\Exceptions\ErrorReporter;
use App\Http\Middleware\RequestLogger;
use App\Mail\ExceptionOccurred;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Cache::flush();

    config()->set('error-reporting.enabled', true);
    config()->set('error-reporting.recipients', ['dev@example.com']);
    config()->set('error-reporting.sensitive_fields', [
        'password',
        'password_confirmation',
        'current_password',
        'token',
    ]);
    config()->set('error-reporting.throttle_seconds', 300);
});

test('error reports send sanitized exception emails with request context', function (): void {
    Mail::fake();

    $request = Request::create('https://librio.test/fail?filter=recent', 'POST', [
        'name' => 'Taylor',
        'password' => 'super-secret',
        'current_password' => 'old-secret',
        'token' => 'abc123',
    ]);

    $request->attributes->set(RequestLogger::REQUEST_ID_ATTRIBUTE, 'req-123');
    $request->headers->set(RequestLogger::HEADER_NAME, 'req-123');
    $request->headers->set('User-Agent', 'Pest');

    app()->instance('request', $request);

    ErrorReporter::report(new RuntimeException('Boom'));

    Mail::assertSent(ExceptionOccurred::class, function (ExceptionOccurred $mail): bool {
        expect($mail->data['request_id'])->toBe('req-123')
            ->and($mail->data['method'])->toBe('POST')
            ->and($mail->data['url'])->toContain('https://librio.test/fail?filter=recent')
            ->and($mail->data['query'])->toBe(['filter' => 'recent'])
            ->and($mail->data['payload']['password'])->toBe('[REDACTED]')
            ->and($mail->data['payload']['current_password'])->toBe('[REDACTED]')
            ->and($mail->data['payload']['token'])->toBe('[REDACTED]')
            ->and($mail->data['payload']['name'])->toBe('Taylor');

        return true;
    });
});

test('error reporting throttles duplicate exception notifications', function (): void {
    Mail::fake();

    $request = Request::create('https://librio.test/fail', 'GET');
    app()->instance('request', $request);

    $exception = new RuntimeException('Repeated failure');

    ErrorReporter::report($exception);
    ErrorReporter::report($exception);

    Mail::assertSent(ExceptionOccurred::class, 1);
});

test('error reports include sanitized contextual exception metadata', function (): void {
    Mail::fake();

    $request = Request::create('https://librio.test/files', 'POST', [
        'token' => 'request-secret',
    ]);
    $request->attributes->set(RequestLogger::REQUEST_ID_ATTRIBUTE, 'req-context');

    app()->instance('request', $request);

    $exception = new class('Upload processing failed', ['file' => 'avatar.png', 'operation' => 'profile-upload', 'token' => 'context-secret'], 'Something went wrong while processing the file.') extends ContextualException {};

    ErrorReporter::report($exception);

    Mail::assertSent(ExceptionOccurred::class, function (ExceptionOccurred $mail): bool {
        expect($mail->data['public_message'])->toBe('Something went wrong while processing the file.')
            ->and($mail->data['exception_context'])->toBe([
                'file' => 'avatar.png',
                'operation' => 'profile-upload',
                'token' => '[REDACTED]',
                'request_id' => 'req-context',
            ]);

        return true;
    });
});
