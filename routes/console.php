<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tunnel', function () {
    $this->info('========================================================');
    $this->info('  CLOUDFLARE TUNNEL - GRAND SULAWESI AIR');
    $this->info('  Target: http://127.0.0.1:8000');
    $this->info('========================================================');

    $binary = base_path('cloudflared.exe');
    if (! file_exists($binary)) {
        $binary = 'cloudflared';
    }

    passthru($binary.' tunnel --url http://127.0.0.1:8000');
})->purpose('Jalankan Cloudflare Tunnel ke http://127.0.0.1:8000');
