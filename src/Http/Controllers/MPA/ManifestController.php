<?php

namespace MuzhikiPro\Auth\Http\Controllers\MPA;

use MuzhikiPro\Auth\Models\MPA\Access;

class ManifestController
{
    public function getManifest(): string
    {
        $data = Access::all()->map(function ($item) {
            return collect($item)->except(['id']);
        });

        return $data;
    }
}