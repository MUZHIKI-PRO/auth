<?php

use Illuminate\Support\Facades\Route;
use MuzhikiPro\Auth\Http\Controllers\MPA\ManifestController;
use MuzhikiPro\Auth\Http\Controllers\MPA\WebhooksController;

Route::middleware(['api'])
    ->prefix('mpa')
    ->group(function () {
        Route::get('manifest', [ManifestController::class, 'getManifest']);
        Route::get('changeRights', [WebhooksController::class, 'changeRights']);
    });
