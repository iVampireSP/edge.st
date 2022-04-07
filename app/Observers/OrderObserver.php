<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

trait OrderObserver
{
    protected static function boot() {
        parent::boot();

        static::created(function ($model) {
            $model->makeVisible(['driver']);
            Log::info('Created', [$model]);
        });

        static::updated(function ($model) {
            Log::info('Updated', [$model]);

        });

        static::deleted(function ($model) {
            Log::info('Deleted', [$model]);
            
        });
    }
}
