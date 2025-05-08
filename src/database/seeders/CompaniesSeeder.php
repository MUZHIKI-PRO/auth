<?php

namespace MuzhikiPro\Auth\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use MuzhikiPro\Auth\Models\MPA\Company;

class CompaniesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->getCompanies() as $company) {
            $company_db = new Company();
            $company_db->id = $company->id;
            $company_db->name = $company->name;
            $company_db->address = $company->address;
            $company_db->yclients_id = $company->yclients_id;
            $company_db->save();
        }
    }

    private function getCompanies()
    {
        return Http::get('https://id.muzhiki.pro/api/companies')->object();
    }

}