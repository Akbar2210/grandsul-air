<?php

namespace App\Providers;

use App\Models\BacaanMeter;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;
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
                $terflagCount = 0;
                if (Schema::hasTable('bacaan_meters')) {
                    $terflagCount = BacaanMeter::where('status_verifikasi', 'ter_flag')->count();
                }

                $notificationsList = [];
                $unreadCount = 0;

                if (Auth::check() && Schema::hasTable('notifikasis')) {
                    $user = Auth::user();
                    $hasUserIdCol = Schema::hasColumn('notifikasis', 'user_id');

                    $query = Notifikasi::query();
                    if ($hasUserIdCol) {
                        if ($user->hasRole('admin')) {
                            $query->where(function ($q) use ($user) {
                                $q->where('user_id', $user->id)
                                    ->orWhereNull('user_id');
                            });
                        } else {
                            $query->where('user_id', $user->id);
                        }
                    }

                    $list = $query->orderByDesc('tgl_kirim')->orderByDesc('id')->take(15)->get();
                    $unreadCount = $list->where('is_read', false)->count();

                    $defaultLink = $user->hasRole('admin')
                        ? route('admin.dashboard')
                        : route('petugas.dashboard');

                    $notificationsList = $list->map(function ($n) use ($defaultLink) {
                        return [
                            'id' => $n->id,
                            'title' => $n->judul ?? 'Notifikasi Sistem',
                            'desc' => $n->pesan,
                            'time' => $n->tgl_kirim ? $n->tgl_kirim->diffForHumans() : 'Baru saja',
                            'unread' => ! (bool) $n->is_read,
                            'type' => $n->tipe ?? 'info',
                            'link' => $n->link ?? $defaultLink,
                        ];
                    })->values()->all();
                }

                $view->with('totalTerflagCount', $terflagCount);
                $view->with('systemNotifications', $notificationsList);
                $view->with('systemUnreadCount', $unreadCount);
            });
        } catch (\Throwable) {
        }
    }
}
