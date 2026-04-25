<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Override;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);
        $this->configureModelGuardrails();
        $this->configureGuestRedirects();

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureGuestRedirects(): void
    {
        RedirectIfAuthenticated::redirectUsing(static function (Request $request): string {
            if (str_starts_with($request->path(), 'admin')) {
                return route('admin.dashboard');
            }

            return route('dashboard');
        });
    }

    protected function configureModelGuardrails(): void
    {
        if ($this->app->isProduction()) {
            Model::shouldBeStrict(false);
            Model::preventLazyLoading();
            Model::handleLazyLoadingViolationUsing($this->reportLazyLoadingViolation(...));

            return;
        }

        Model::handleLazyLoadingViolationUsing(null);
        Model::shouldBeStrict($this->app->isLocal() || $this->app->runningUnitTests());
    }

    protected function reportLazyLoadingViolation(Model $model, string $relation): void
    {
        Log::warning('eloquent.lazy_loading_violation', [
            'model' => $model::class,
            'relation' => $relation,
            'request_id' => request()?->attributes->get('request_id'),
        ]);
    }
}
