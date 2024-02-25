<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('customers')->delete();

        \DB::table('customers')->insert(array (
            0 =>
            array (
                'id' => 1,
                'numdoc' => '10133423',
                'nomrazonsocial' => 'ticom perux',
                'address' => 'av petit thouars 1255',
                'phone' => '266250',
                'movil' => '996929478',
                'email' => 'mcaal@hotmail.com',
                'contact' => 'mike',
                'state' => 1,
                'user_id' => 1,
                'tipodocumento_id' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'numdoc' => '10133420',
                'nomrazonsocial' => 'btecperu',
                'address' => 'av raranibar 122',
                'phone' => '2662540',
                'movil' => '996929478',
                'email' => 'mmm@mmm.com',
                'contact' => 'james',
                'state' => 1,
                'user_id' => 1,
                'tipodocumento_id' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));


    }
}
