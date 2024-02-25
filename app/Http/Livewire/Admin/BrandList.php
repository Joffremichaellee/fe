<?php

namespace App\Http\Livewire\Admin;

use App\Models\Brand;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Exports\BrandExport;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\validation\Rule;
use App\Models\Configuration;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BrandList extends Component
{
    use WithPagination; //para paginacion
    use AuthorizesRequests; //para permisos
    use WithFileUploads; //para la carga de imagenes
    public $search, $image, $brand, $state, $identificador; //identificador para recargar la imagen
    public $order, $title, $description, $keywords;
    public $sort = 'id';
    public $direction = 'desc';
    public $cant = '10';
    public $open_edit = false;
    public $open_view = false;
    public $readyToLoad = false; //para controlar el preloader inicia en false
    public $selectedBrands = []; //para eliminar en grupo
    public $selectAll = false; //para eliminar en grupo

    protected $listeners = ['render', 'delete'];

    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'id'],
        'direction' => ['except' => 'desc'],
        'search' => ['except' => ''],
    ];


    // Método para seleccionar/deseleccionar todos
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedBrands = Brand::pluck('id')->mapWithKeys(function ($id) {
                return [$id => true];
            })->toArray();
        } else {
            $this->selectedBrands = [];
        }
        //mapWithKeys(function ($id) { return [$id => true]; })
        //Estamos utilizando el método mapWithKeys para transformar el array de IDs en un array asociativo donde
        //cada ID es la clave y el valor es establecido como verdadero. Esto se hace para representar las marcas seleccionadas
    }


    // Método para eliminar marcas seleccionadas
    public function deleteSelected()
    {
        $this->authorize('delete', Brand::class); // Asegúrate de tener permisos para eliminar

        $selectedIds = array_keys(array_filter($this->selectedBrands));

        if ($selectedIds) {
            Brand::whereIn('id', $selectedIds)->delete();

            $this->resetSelected();
            $this->emit('alert', 'Las marcas seleccionadas se eliminaron correctamente');
        }else {
            $this->emit('alert', 'No hay marcas seleccionadas');
        }
    }

    // Método para restablecer la selección después de eliminar
    private function resetSelected()
    {
        $this->selectAll = false;
        $this->selectedBrands = [];
    }


    public function generateReport()
    {
        //dd("Prueba");
        //return Excel::download(new BrandExport(), 'marcas.xlsx');
        //return Excel::download(new BrandExport(), 'marcas.csv', \Maatwebsite\Excel\Excel::CSV);
        //return (new BrandExport())->download();//ponemos la interfas responsable en brandExport y no necesitamos poner downloas
        return new BrandExport();
    }




    public function mount()
    {
        $this->identificador = rand(); //identificador aleatorio, se usa en el id de la imagen osea en el inputfile
        $this->brand = new Brand(); //se hace para inicializar el objeto e indicar que image es
        $this->image = "";
    }

    public function updatingSearch()
    {
        $this->resetPage();
        //RESETEA la paginación, updating() cuando se cambia una de las propiedades  updatingSearch() cuando se cambia la propiedad search
    }


    /*  'brand.name'=> 'required',Rule::unique('brands')->ignore($this->brand->id) */

    /*estas reglas es para la edicion */
    protected $rules = [
        'brand.name' => 'required',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        //'brand.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Agregamos 'nullable' para permitir valores nulos
        'brand.state' => 'required',
        'brand.company_id' => '',
        'brand.order' => '',
        'brand.title' => '',
        'brand.description' => '',
        'brand.keywords' => '',
    ];

    /*para cargar la consulta mientras no carga muestra el spiner */
    public function loadBrands()
    {
        $this->readyToLoad = true; //se activa una vez cargado la consulta, esto lo hace laravel por nosotros
    }

    public function render()
    {
        $this->authorize('view', new Brand);
        $companyId = auth()->user()->employee->company->id;

        if ($this->readyToLoad) {
            $brands = Brand::where('company_id', $companyId)
                ->where('name', 'like', '%' . $this->search . '%')
                ->when($this->state, function ($query) { /* Esta línea utiliza el método when de Laravel para condicionalmente aplicar una cláusula where en la consulta Eloquent. Si $this->state es verdadero (es decir, tiene un valor que se evalúa como verdadero en PHP), entonces se agrega la cláusula where que filtra los registros donde el campo state es igual a 1. */
                    return $query->where('state', 1);
                })
                ->orderBy($this->sort, $this->direction)
                ->paginate($this->cant);
        } else {
            $brands = [];
        }
        return view('livewire.admin.brand-list', compact('brands'));
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


    public function activar(Brand $brand)
    {

        $this->authorize('update', $this->brand);

        $this->brand = $brand;

        $this->brand->update([
            'state' => 1
        ]);
    }

    public function desactivar(Brand $brand)
    {
        $this->authorize('update', $this->brand); //tenemos que mandar el error a una pagina
        $this->brand = $brand;

        $this->brand->update([
            'state' => 0
        ]);
    }

    public function delete(Brand $brand)
    {
        $this->authorize('update', $brand);
        $brand->delete();
    }

    public function edit(Brand $brand)
    {
        $this->brand = $brand;
        $this->open_edit = true;
    }

    public function show(Brand $brand)
    {
        $this->brand = $brand;
        $this->open_view = true;
    }


    public function cancelar()
    {
        $this->reset('open_edit', 'image');
        $this->identificador = rand();
        //$this->open_edit = false;
    }

    public function cerrar()
    {
        $this->reset('open_view');
        //$this->identificador = rand();
        //$this->open_edit = false;
    }

    public function update()
    {
        $this->authorize('update', $this->brand);

        $this->validate();
        //$this->brand->image = "/storage/brands/default.jpg";
        if ($this->image) { //verifica si selecciono imagen
            if ($this->brand->image) { //comprobamos que exista imagen en la tabla brands
                Storage::delete([$this->brand->image]);
            }

            $configuration = Configuration::first();
            if ($configuration->typeimage == 3) {
                $this->brand->image = Storage::url($this->image->store('brands', 'public'));
            } elseif ($configuration->typeimage == 2) {
            } elseif ($configuration->typeimage == 1) {
            }
        }

        // $this->validate();

        //convierto brand.name en mayuscula
        $this->brand->name = strtoupper($this->brand->name);
        $this->brand->company_id = auth()->user()->employee->company->id;
        //es otra forma de actualizar
        $this->brand->save();

        //esto es la forma de guardar la actiualizacion dato por dato
        /*  $this->brand->save([
            'name' => $this->brand->name,
            'slug' => Str::slug($this->brand->slug),
            'state' => $this->brand->statee,
            'order' => $this->brand->order,
            'comany_id' => auth()->user()->employee->company->id,
            //'image' => $image,
            'image' => $this->brand->image,
        ]); */

        $this->reset('open_edit', 'image');
        $this->identificador = rand();
        //$this->emitTo('show-brands', 'render');
        $this->emit('alert', 'La marca se modificó correctamente');
    }
}
