<?php

use App\Services\ClickPesaOrderSyncService;
use App\Services\RobotArmCommandService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('clickpesa:sync-orders {--limit=50}', function (ClickPesaOrderSyncService $syncService) {
    $limit = max(1, (int) $this->option('limit'));
    $synced = $syncService->syncAllProcessingOrders($limit);

    $this->info("Synced {$synced} ClickPesa order(s).");
})->purpose('Sync pending ClickPesa order statuses from the ClickPesa API');

Artisan::command('robot:poll', function (RobotArmCommandService $robot) {
    if (! $robot->isConfigured()) {
        $this->info('Robot arm polling is disabled or not configured.');

        return 0;
    }

    if (! $robot->hasActiveCommand()) {
        $this->info('No active robot command to poll.');

        return 0;
    }

    $result = $robot->pollStatus();

    if (! $result['ok']) {
        $this->error($result['message'] ?: 'Robot status poll failed.');

        return 1;
    }

    $this->info('Robot status: '.$result['status']);

    return 0;
})->purpose('Poll the ESP32 and synchronize the active robot command');

Schedule::command('robot:poll')
    ->everyFiveSeconds()
    ->withoutOverlapping();
