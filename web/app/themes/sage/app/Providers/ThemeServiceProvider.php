<?php

namespace App\Providers;

use App\Admin\ContactSettingsPage;
use App\Admin\DeliveryTimerSettingsPage;
use App\Admin\ProductAttributeIcons;
use App\Api\Categories;
use App\Api\Healthcheck;
use App\Blocks\Blocks;
use App\Media\ImageHelper;
use App\Support\Context;
use App\Support\DeliveryTimer;
use Illuminate\Support\Facades\Blade;
use Roots\Acorn\Sage\SageServiceProvider;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton(Context::class);
        $this->app->singleton(DeliveryTimer::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        Categories::boot();
        Healthcheck::boot();
        Blocks::boot();
        ContactSettingsPage::boot();
        DeliveryTimerSettingsPage::boot();
        ProductAttributeIcons::boot();
        DeliveryTimer::boot();
        ImageHelper::boot();
        Blade::directive('id', function ($expression) {
            return "<?php if (!empty($expression)): ?>id=\"<?php echo e($expression); ?>\"<?php endif; ?>";
        });
    }
}
