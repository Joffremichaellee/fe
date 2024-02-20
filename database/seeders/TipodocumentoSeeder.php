<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tipodocumento;

class TipodocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tipodocumento::create([
            'name' => 'documento nacional de identidad',
            'codigo' => '1',
            'abbreviation'=> 'DNI',
            'state' => 1,
        ]);


        Tipodocumento::create([
            'name' => 'Carnet de Extranjeria',
            'codigo' => '4',
            'abbreviation'=> 'CE',
            'state' => 1,
        ]);

        Tipodocumento::create([
            'name' => 'Registro Unico del Contribuyente',
            'codigo' => '6',
            'abbreviation'=> 'RUC',
            'state' => 1,
        ]);

    }
}
