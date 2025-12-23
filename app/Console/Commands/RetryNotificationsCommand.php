<?php

namespace App\Console\Commands;

use App\Actions\Transfer\RetryFailedNotificationsAction;
use Illuminate\Console\Command;

use function app;

class RetryNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:retry-notifications-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        app(RetryFailedNotificationsAction::class)->execute();
    }
}
