<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }


    public function boot()
    {
        //
        //\Livewire\Livewire::dev();

        //aqui se valida para que no se repita la combinacion name company_id  para que las empresas puedan crear marcas con el mismo nombre
        //ejemplo la empresa x puede tener la marca adidas la empresa y tambien la marca adidas
        Validator::extend('unique_brand', function ($attribute, $value, $parameters, $validator) {
            $company_id = auth()->user()->employee->company->id ?? null;

            // Verifica la unicidad en la combinación de name y company_id solo si la empresa no ha registrado esa marca
            return !DB::table('brands')
                ->where('name', $value)
                ->where('company_id', $company_id)
                ->exists();
        });


    }
}
