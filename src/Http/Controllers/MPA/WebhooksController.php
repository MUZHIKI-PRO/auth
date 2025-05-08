<?php

namespace MuzhikiPro\Auth\Http\Controllers\MPA;

use Illuminate\Http\Request;
use MuzhikiPro\Auth\Models\MPA\Access;

class WebhooksController
{
    public function addUser(Request $request)
    {
        $user = $request->input('user');
    }

    public function changeRights(Request $request)
    {
        if($request->input('event') == 'rightsUpdated') return;
        $hash = hash('sha256', $request->input('user_id').'.'.json_encode($request->input('data')).'.'.config('muzhiki-auth.signature'));
        if($request->input('hash') != $hash) abort(401);

        $user = config('muzhiki-auth.user_model')::where('yclients_user_id', $request->input('y_user_id'))->first();
        if (!$user) {
            $user = config('muzhiki-auth.user_model')::where('yclients_id', $request->input('y_id'))->first();
        }
        if (!$user) {
            $user = config('muzhiki-auth.user_model')::where('email', $request->input('email'))->first();
        }
        if (!$user) {
            $user = config('muzhiki-auth.user_model')::where('phone', $request->input('phone'))->first();
        }
        if (!$user) return;

        $user->setAccesses($request->input('data'));
    }
}