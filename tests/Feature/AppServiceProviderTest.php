<?php

declare(strict_types=1);

use App\Http\Middleware\RequestLogger;
use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    Model::shouldBeStrict(false);
    Model::handleLazyLoadingViolationUsing(null);
    Model::preventLazyLoading(false);
});

afterEach(function (): void {
    Model::shouldBeStrict(false);
    Model::handleLazyLoadingViolationUsing(null);
    Model::preventLazyLoading(false);
});

test('strict model guardrails are enabled while testing', function (): void {
    $provider = new class(app()) extends AppServiceProvider
    {
        public function enableTestingStrictMode(): void
        {
            Model::handleLazyLoadingViolationUsing(null);
            Model::shouldBeStrict(true);
        }
    };

    $provider->enableTestingStrictMode();

    expect(Model::preventsLazyLoading())->toBeTrue()
        ->and(Model::preventsSilentlyDiscardingAttributes())->toBeTrue()
        ->and(Model::preventsAccessingMissingAttributes())->toBeTrue();
});

test('lazy loading violations are surfaced outside production logging mode', function (): void {
    $provider = new class(app()) extends AppServiceProvider
    {
        public function enableTestingStrictMode(): void
        {
            Model::handleLazyLoadingViolationUsing(null);
            Model::shouldBeStrict(true);
        }
    };

    $provider->enableTestingStrictMode();

    $model = new class extends Model {};

    $model->exists = true;

    $invokeViolation = Closure::bind(
        fn () => $this->handleLazyLoadingViolation('profile'),
        $model,
        $model,
    );

    $this->expectException(LazyLoadingViolationException::class);
    $this->expectExceptionMessage('lazy load [profile]');

    $invokeViolation();
});

test('production lazy loading handler logs instead of throwing', function (): void {
    Log::spy();

    request()->attributes->set(RequestLogger::REQUEST_ID_ATTRIBUTE, 'req-prod');

    $provider = new class(app()) extends AppServiceProvider
    {
        public function enableProductionLazyLoadingMode(): void
        {
            Model::shouldBeStrict(false);
            Model::preventLazyLoading();
            Model::handleLazyLoadingViolationUsing($this->reportLazyLoadingViolation(...));
        }
    };

    $provider->enableProductionLazyLoadingMode();

    $model = new class extends Model {};

    $model->exists = true;

    $invokeViolation = Closure::bind(
        fn () => $this->handleLazyLoadingViolation('profile'),
        $model,
        $model,
    );

    $invokeViolation();

    Log::shouldHaveReceived('warning')
        ->once()
        ->with('eloquent.lazy_loading_violation', [
            'model' => $model::class,
            'relation' => 'profile',
            'request_id' => 'req-prod',
        ]);
});
