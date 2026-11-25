<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tunnel', function () {
    $this->info('========================================================');
    $this->info('  CLOUDFLARE TUNNEL - GRAND SULAWESI AIR');
    $this->info('  Menghubungkan ke http://127.0.0.1:8000 ...');
    $this->info('========================================================');
    $this->line('Sedang membuat link publik, mohon tunggu sekitar 3-5 detik...');

    $binary = base_path('cloudflared.exe');
    if (! file_exists($binary)) {
        $binary = 'cloudflared';
    }

    $linkFound = false;
    $process = new Process([$binary, 'tunnel', '--url', 'http://127.0.0.1:8000']);
    $process->setTimeout(null);
    $process->run(function ($type, $buffer) use (&$linkFound) {
        if (! $linkFound && preg_match('/(https:\/\/[a-z0-9\-]+\.trycloudflare\.com)/i', $buffer, $matches)) {
            $linkFound = true;
            $url = $matches[1];
            $this->newLine();
            $this->info('====================================================================');
            $this->info('  LINK PUBLIK ANDA SUDAH AKTIF:');
            $this->info("  👉  {$url}");
            $this->info('====================================================================');
            $this->comment('  * Salin dan buka link di atas pada browser HP atau laptop mana saja.');
            $this->comment('  * Jangan tutup jendela terminal ini selama web masih digunakan.');
            $this->comment('  * Tekan tombol Ctrl+C jika ingin mematikan koneksi.');
            $this->newLine();
        }
    });
})->purpose('Jalankan Cloudflare Tunnel ke http://127.0.0.1:8000');
