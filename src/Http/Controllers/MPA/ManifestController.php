<?php

namespace MuzhikiPro\Auth\Http\Controllers\MPA;

use MuzhikiPro\Auth\Models\MPA\Access;

class ManifestController
{
    public function getManifest(): string
    {
        return Access::all()->except(['id']);
    }
}