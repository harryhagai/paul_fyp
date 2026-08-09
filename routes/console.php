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

    $result = $robot->processQueue();

    if (! $result['ok']) {
        $this->error($result['message'] ?: 'Robot status poll failed.');

        return 1;
    }

    $this->info($result['queue_empty']
        ? 'Robot queue is empty.'
        : 'Robot queue status: '.$result['status']);

    return 0;
})->purpose('Process the robot PICK queue and synchronize the active command');

Schedule::command('robot:poll')
    ->everyFiveSeconds()
    ->withoutOverlapping();
