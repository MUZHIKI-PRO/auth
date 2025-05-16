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
        if($request->input('event') != 'rightsUpdated') return;
        $hash = hash('sha256', $request->input('user_id').'.'.json_encode($request->input('data')).'.'.config('muzhiki-auth.signature'));
        if($request->input('hash') != $hash) abort(401);

        $userModel = config('muzhiki-auth.user_model');
        $query     = $userModel::query();

// Добавляем условия только если в $obj есть данные
        if (!empty($obj->yclients_user_ids) && count($obj->yclients_user_ids) > 0) {
            $query->orWhereIn('yclients_user_id', $obj->yclients_user_ids);
        }

        if (!empty($obj->yclients_ids) && count($obj->yclients_ids) > 0) {
            $query->orWhereIn('yclients_id', $obj->yclients_ids);
        }

        if (!empty($obj->emails) && count($obj->emails) > 0) {
            $query->orWhereIn('email', $obj->emails);
        }

        if (!empty($obj->phones) && count($obj->phones) > 0) {
            $query->orWhereIn('phone', $obj->phones);
        }

        $user = $query->first();

        if (!$user) return;

        $user->setAccesses($request->input('data'));
    }
}