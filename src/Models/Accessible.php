<?php

namespace MuzhikiPro\Auth\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use MuzhikiPro\Auth\Models\MPA\Access;
use MuzhikiPro\Auth\Models\MPA\Company;

trait Accessible
{
    /**
     * @return mixed Возвращает коллекцию доступов пользователя
     */
    public function accesses()
    {
        return $this->belongsToMany(Access::class, 'mpa_access_user')->withPivot('company_id');
    }

    /**
     * @return mixed Возвращает коллекцию компаний, к которым у пользователя есть доступ
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'mpa_access_user');
    }

    /**
     * Получает информацию о пользователе по access-токену (полученному после редиректа с МУЖИКИ ПРО ID)
     * @param string $token
     * @return config
     * @throws ConnectionException
     * @throws RequestException
     */
    public static function getUserFromToken(string $token)
    {
        $response = Http::withToken(config('muzhiki-auth.client_secret'))
            ->withHeader('App-Id', config('muzhiki-auth.client_id'))
            ->post(config('muzhiki-auth.auth_service_endpoint').'/api/getUser',
                [
                    'token' => $token
                ]
            );
        $response->throw();
        return self::getUser($response->object());
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function setTgId(string $telegram_id): void
    {
        $response = Http::withToken(config('muzhiki-auth.client_secret'))
            ->withHeader('App-Id', config('muzhiki-auth.client_id'))
            ->post(config('muzhiki-auth.auth_service_endpoint').'/api/set-telegram',
                [
                    'muzhikipro_user_id' => $this->muzhikipro_user_id,
                    'telegram_id' => $telegram_id
                ]
            );
        $response->throw();
    }

    public function getStaffIdForCompany(string $company): string
    {
        $response = Http::withToken(config('muzhiki-auth.client_secret'))
            ->withHeader('App-Id', config('muzhiki-auth.client_id'))
            ->get(config('muzhiki-auth.auth_service_endpoint').'/api/user/'.$this->id.'/company/'.$company.'staffId',);
        $response->throw();
        return $response->body();
    }

    /**
     * Обновляет и возвращает пользователя по объекту от МУЖИКИ ПРО ID
     * @param $obj
     * @return config
     */
    protected static function getUser($obj)
    {

        $userModel = config('muzhiki-auth.user_model');
        $query     = $userModel::query();

        // Добавляем условия только если в $obj есть данные
        if (!empty($obj->user_id)) {
            $query->where('muzhikipro_user_id', $obj->user_id);
        }

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

// выполняем поиск
        $user = $query->first();

// если не нашли — создаём, иначе берём старого
        if (! $user) {
            $user = new $userModel;
            $user->password = Hash::make(Str::random(16));
        }

        $user->name = $obj->name;
        $user->email = count($obj->emails) > 0 && $obj->emails[0] ? $obj->emails[0] : null;
        $user->phone = count($obj->phones) > 0 && $obj->phones[0] ? $obj->phones[0] : null;
        $user->yclients_user_id = null;
        $user->yclients_id = null;
        $user->muzhikipro_user_id = $obj->user_id;
        $user->save();


        $user->setAccesses($obj->accesses);

        Auth::login($user);
        return $user;
    }

    /**
     * Устанавливает доступы для пользователя
     * @param $data
     * @return void
     */
    public function setAccesses($data)
    {
        $accesses_map = Access::select(['id', 'key'])->get();
        $map = [];
        foreach ($accesses_map as $item){
            $map[$item->key] = $item->id;
        }

        $this->accesses()->detach();
        foreach ($data as $company){
            $company = (object) $company;
            $company->pivot = (object) $company->pivot;
            $this->accesses()->attach($map[$company->key], ['company_id' => $company->pivot->company_id]);
        }
    }

    /**
     * Возвращает результат проверки наличия определённого тега у пользователя
     * @param string $access
     * @return bool
     */
    public function hasAccess(string $access) : bool
    {
        return $this->accesses()->where('key', $access)->exists();
    }

    public function getCompanyWithAccess(string $access)
    {
        return $this->accesses()->where('key', $access)->get()->pluck('pivot.company_id');
    }

    /**
     * Возвращает результат проверки наличия определённого тега у пользователя для конкретной компании
     * @param string $access
     * @param int $company_id
     * @return bool
     */
    public function hasAccessForCompany(string $access, int $company_id) : bool
    {
        return $this->accesses()->where('key', $access)->wherePivot('company_id', $company_id)->exitst();
    }

    /**
     * Возвращает коллекцию пользователей с определённой ролью для определённой компании
     * @param Builder $query
     * @param string $access
     * @param int $company_id
     * @return Builder
     */
    public function scopeWhereHasAccessForCompany(Builder $query, string $access, int $companyId): Builder
    {
        return $query->whereHas('accesses', function (Builder $q) use ($access, $companyId) {
            $q->where('key', $access)
                ->where('mpa_access_user.company_id', $companyId);
        });
    }

    /**
     * Возвращает коллекцию пользователей с определённой ролью
     * @param Builder $query
     * @param string $access
     * @return Builder
     */
    public function scopeWhereHasAccess(Builder $query, string $access): Builder
    {
        return $query->whereHas('accesses', function (Builder $q) use ($access) {
            $q->where('key', $access);
        });
    }

}