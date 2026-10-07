<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void 
    {
        //
    }

    public function boot(): void
    {
        // 1. PAKSA TIMEZONE KE JAKARTA (WIB) DI SINI
        date_default_timezone_set('Asia/Jakarta');

        // 2. Blade directive untuk format Rupiah
        Blade::directive('rp', fn ($e) => "<?php echo 'Rp'.number_format((float) ($e), 0, ',', '.'); ?>");
    }
}