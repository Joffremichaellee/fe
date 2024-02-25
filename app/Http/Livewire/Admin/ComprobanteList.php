<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Comprobante;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ComprobanteList extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    //public $shopping;
    public $search;
    public $sort = 'id';
    public $direction = 'desc';
    public $cant = '10';
    public $readyToLoad = false; //para controlar el preloader inicia en false

    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'id'],
        'direction' => ['except' => 'desc'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
        //RESETEA la paginación, updating() cuando se cambia una de las propiedades  updatingSearch() cuando se cambia la propiedad search
    }

    public function loadComprobantes()
    {
        $this->readyToLoad = true;
    }


    /* public function render()
    {
        if ($this->readyToLoad) {
            $comprobantes = Comprobante::with('customer', 'local')->addSelect([
                'nomrazonsocial' => Customer::select('nomrazonsocial')
                    ->whereColumn('id', 'comprobantes.customer_id')
            ])->where('serie', 'like', '%' . $this->search . '%')->orWhereHas('customer', function (Builder $query) {
                $query->where('nomrazonsocial', 'like', '%' . $this->search . '%');
            })->orderBy($this->sort, $this->direction)->paginate($this->cant);
        } else {
            $comprobantes = [];
        }

        return view('livewire.admin.comprobante-list', compact('comprobantes'));
    } */


    public function render()
    {
        if ($this->readyToLoad) {
<<<<<<< HEAD
            $company_id = auth()->user()->employee->company->id;
=======

>>>>>>> 530b2f79ee27d57ab04a035026d813a135fe0b96
            // with('customer', 'local') para la carga ansiosa n+1
            $comprobantes = Comprobante::with('customer', 'local')->addSelect([
                'nomrazonsocial' => Customer::select('nomrazonsocial')
                    ->whereColumn('id', 'comprobantes.customer_id')
            ])->where('company_id', $company_id)
              ->where(function ($query) {
                $query->whereHas('boleta', function (Builder $boletaQuery) {
                    $boletaQuery->where('serienumero', 'like', '%' . $this->search . '%');
                                //->orWhere('numero', 'like', '%' . $this->search . '%');
                })->orWhereHas('customer', function (Builder $customerQuery) {
                    $customerQuery->where('nomrazonsocial', 'like', '%' . $this->search . '%');
                });
            })->orderBy($this->sort, $this->direction)->paginate($this->cant);

        } else {
            $comprobantes = [];
        }


        /* return view('livewire.admin.comprobante-list', [
            'comprobantes' => $comprobantes->map(function ($comprobante) {
                $comprobante->formattedFechaEmision = Carbon::parse($comprobante->fechaemision)->format('d/m/Y');
                return $comprobante;
            }),
        ]); */

        return view('livewire.admin.comprobante-list', compact('comprobantes'));
    }







    public function order($sort)
    {
        if ($this->sort == $sort) {
            if ($this->direction == 'desc') {
                $this->direction = 'asc';
            } else {
                $this->direction = 'desc';
            }
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        }
    }
}
