<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\{DB, Cache, Queue, Redis, Storage, Artisan};
use Carbon\Carbon;

class HealthService
{
    public function getStatus(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'redis'    => $this->checkRedis(),
            'queue'    => $this->checkQueue(),
            'storage'  => $this->checkStorage(),
            'scheduler' => $this->checkScheduler(),
            'server'   => $this->getServerInfo(),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $version = DB::selectOne('SELECT VERSION() as version')->version;
            return ['status' => 'ok', 'message' => "MySQL {$version}", 'latency_ms' => $this->measureLatency(fn() => DB::select('SELECT 1'))];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            Redis::ping();
            $ms = round((microtime(true) - $start) * 1000, 2);
            return ['status' => 'ok', 'message' => 'Connected', 'latency_ms' => $ms];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $failed = DB::table('failed_jobs')->count();
            $pending = DB::table('jobs')->count();
            return [
                'status'  => $failed > 10 ? 'warning' : 'ok',
                'pending' => $pending,
                'failed'  => $failed,
                'message' => "{$pending} pending, {$failed} failed",
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        try {
            $writable = is_writable(storage_path());
            $free = disk_free_space('/');
            $total = disk_total_space('/');
            $usedPct = round((($total - $free) / $total) * 100, 1);
            return [
                'status'    => (!$writable || $usedPct > 90) ? 'warning' : 'ok',
                'writable'  => $writable,
                'used_pct'  => $usedPct,
                'free_gb'   => round($free / 1073741824, 2),
                'message'   => "{$usedPct}% disk used",
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkScheduler(): array
    {
        $lastRun = Cache::get('scheduler_last_run');
        if (!$lastRun) {
            return ['status' => 'warning', 'message' => 'Never recorded. Is cron running?'];
        }
        $diff = Carbon::parse($lastRun)->diffInMinutes(now());
        return [
            'status'   => $diff > 5 ? 'warning' : 'ok',
            'last_run' => $lastRun,
            'message'  => "Last ran {$diff}m ago",
        ];
    }

    private function getServerInfo(): array
    {
        return [
            'php'     => PHP_VERSION,
            'laravel' => app()->version(),
            'os'      => PHP_OS,
            'uptime'  => @file_get_contents('/proc/uptime') ? trim(explode(' ', file_get_contents('/proc/uptime'))[0]) . 's' : 'N/A',
            'memory_mb' => round(memory_get_usage(true) / 1048576, 2),
        ];
    }

    private function measureLatency(callable $fn): float
    {
        $start = microtime(true);
        $fn();
        return round((microtime(true) - $start) * 1000, 2);
    }
}