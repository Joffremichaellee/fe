<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    //relacion de uno a muchos inversa
    public function local_tipocomprobante()
    {
        return $this->belongsTo(Local_tipocomprobante::class);
    }


    //relacion de uno a muchos polimorfica
    /* public function comprobanteable()
    {
        return $this->morphTo();
    } */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    //venta interna
    public function tipodeoperacion()
    {
        return $this->belongsTo(Tipodeoperacion::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function paymenttype()
    {
        return $this->belongsTo(Paymenttype::class);
    }

    public function local()
    {
        return $this->belongsTo(Local::class);
    }

    //relacion de uno a uno
    public function boleta()
    {
        return $this->hasOne(Boleta::class, 'comprobante_id');
    }

    //ruc, dni
    public function tipodocumento()
    {
        return $this->belongsTo(Tipodocumento::class);
    }

        //factura, boleta
        public function tipocomprobante()
        {
            return $this->belongsTo(Tipocomprobante::class);
        }

}
