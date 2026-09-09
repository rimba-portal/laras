# Installation

1. Copy these files into the existing `rimba/laras` package.
2. Merge `src/SyncServiceProvider.additions.php` into `SyncServiceProvider`.
3. Merge `config/workforce-sync.php` as package config.
4. Ensure the WFM workforce-core additions are installed first.
5. Run `php artisan migrate`.
6. Run `php artisan workforce:sync 6` for the HRDB `ApiConfig` ID shown in the existing sample command.

## Scheduler

Add to the application scheduler:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('workforce:sync 6')->daily()->withoutOverlapping();
```

## Safety

The package does not copy HRDB password, salt, token, cryptographic, biometric, or authentication fields into Staff attributes or snapshots.
