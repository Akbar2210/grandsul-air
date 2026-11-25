<?php

namespace App\Providers;

use App\Models\BacaanMeter;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->header('x-forwarded-proto') === 'https' || str_contains(request()->header('host', ''), 'trycloudflare.com')) {
            URL::forceScheme('https');
            Vite::useHotFile(storage_path('framework/cache/disabled_hot'));
        }

        try {
            View::composer(['layouts.admin', 'admin.*', 'petugas.*'], function ($view) {
                static $terflagCount = null;
                static $notificationsData = null;

                if ($terflagCount === null) {
                    if (Schema::hasTable('bacaan_meters')) {
                        $terflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();
                    } else {
                        $terflagCount = 0;
                    }
                }

                if ($notificationsData === null) {
                    if (Schema::hasTable('notifikasis')) {
                        $list = Notifikasi::where('is_read', false)->latest('tgl_kirim')->take(15)->get();
                        $unreadCount = $list->count();
                        $notificationsData = [
                            'list' => $list->map(function ($n) {
                                return [
                                    'id' => $n->id,
                                    'title' => $n->judul ?? 'Notifikasi Sistem',
                                    'desc' => $n->pesan,
                                    'time' => $n->tgl_kirim ? $n->tgl_kirim->diffForHumans() : 'Baru saja',
                                    'unread' => true,
                                    'type' => $n->tipe ?? 'info',
                                    'link' => $n->link ?? route('admin.dashboard'),
                                ];
                            })->values()->all(),
                            'unread_count' => $unreadCount,
                        ];
                    } else {
                        $notificationsData = ['list' => [], 'unread_count' => 0];
                    }
                }

                $view->with('totalTerflagCount', $terflagCount);
                $view->with('systemNotifications', $notificationsData['list']);
                $view->with('systemUnreadCount', $notificationsData['unread_count']);
            });
        } catch (\Throwable) {
        }
    }
}
