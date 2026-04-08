<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->requestLogPath = storage_path('logs/request-test.log');

    File::delete($this->requestLogPath);

    config()->set('logging.channels.request', [
        'driver' => 'single',
        'path' => $this->requestLogPath,
        'level' => 'debug',
        'replace_placeholders' => true,
    ]);

    resolve('log')->forgetChannel('request');

    Route::middleware('web')->post('/__request-logger-test', fn (Request $request) => response()->json([
        'received' => $request->input('name'),
    ]));

    Route::middleware('web')->get('/__request-logger-header-test', fn () => response()->noContent());
});

afterEach(function (): void {
    File::delete($this->requestLogPath);
});

test('normal requests are written to the request log with sanitized data', function (): void {
    $response = $this->post('/__request-logger-test?filter=recent', [
        'name' => 'Taylor',
        'password' => 'super-secret',
        'current_password' => 'old-secret',
        'token' => 'abc123',
    ]);

    $response->assertSuccessful();

    expect(File::exists($this->requestLogPath))->toBeTrue();

    $contents = File::get($this->requestLogPath);

    expect($contents)
        ->toContain('request.completed')
        ->toContain('"method":"POST"')
        ->toContain('"path":"/__request-logger-test"')
        ->toContain('"status":200')
        ->toContain('"filter":"recent"')
        ->toContain('"password":"[REDACTED]"')
        ->toContain('"current_password":"[REDACTED]"')
        ->toContain('"token":"[REDACTED]"')
        ->not->toContain('super-secret')
        ->not->toContain('old-secret')
        ->not->toContain('abc123');

    preg_match('/"request_id":"([^"]+)"/', $contents, $matches);

    expect($matches[1] ?? null)->not->toBeNull()
        ->and(Str::isUuid($matches[1]))->toBeTrue();
});

test('responses include the request id header', function (): void {
    $response = $this->get('/__request-logger-header-test');

    $response->assertNoContent();

    $requestId = $response->headers->get('X-Request-Id');

    expect($requestId)->not->toBeNull()
        ->and(Str::isUuid($requestId))->toBeTrue();
});

test('health checks are excluded from request logging', function (): void {
    $response = $this->get('/up');

    $response->assertOk();

    expect(File::exists($this->requestLogPath))->toBeFalse();
});
