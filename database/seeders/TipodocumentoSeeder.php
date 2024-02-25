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
<<<<<<< HEAD
            'name' => 'Doc.trib.no.dom.sin.ruc',
            'codigo' => '0',
            'abbreviation'=> 'DOC',
            'state' => 1,
        ]);

        Tipodocumento::create([
            'name' => 'Documento Nacional de identidad',
=======
            'name' => 'documento nacional de identidad',
>>>>>>> 530b2f79ee27d57ab04a035026d813a135fe0b96
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
<<<<<<< HEAD
        Tipodocumento::create([
            'name' => 'Pasaporte',
            'codigo' => '7',
            'abbreviation'=> 'Pasaporte',
            'state' => 1,
        ]);
=======
>>>>>>> 530b2f79ee27d57ab04a035026d813a135fe0b96

    }
}
