<?php

namespace App\Http\Livewire\Admin;

use Carbon\Carbon;
use App\Models\Local;
use App\Models\Boleta;
use App\Models\Company;
use App\Models\Product;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Impuesto;
use App\Models\Temporal;
use App\Models\Comprobante;
use App\Models\Tipodocumento;
use App\Services\SunatService;
use GuzzleHttp\Promise\Create;
use App\Models\Tipocomprobante;
use Illuminate\Support\Collection;
use App\Models\Comprobante_Product;
use App\Models\Local_tipocomprobante;
use Luecano\NumeroALetras\NumeroALetras;

class ComprobanteCreate extends Component
{
    //public $msg = '';
    //public $itemsQuantity;
    public $company;
    public $tipodocumento_id="", $customer_id = "", $local_id = "", $tipocomprobante_id = "", $local_tipocomprobante_id, $company_id, $employee_id, $fechaemision, $nota;
    public $serie, $numero, $serienumero, $fechavencimiento, $total, $comprobante_id, $paymenttype_id = "", $currency_id = "";
    public $moneda;
    public $subtotal, $tipodecambio_id;
    public $search, $boleta;
    public $factoricbper, $igv;
    public $mtoopergravadas, $mtooperexoneradas, $mtooperinafectas, $mtooperexportacion, $mtoopergratuitas, $mtoigv, $mtoigvgratuitas, $icbper, $totalimpuestos;
    public $valorventa, $subtotall, $mtoimpventa, $redondeo, $legends;
    public $totalenletras;


    //public $salesCartInstance = 'salesCart';
    //public $carts = [];
    protected $listeners = ['delete', 'limpiar'];

    public function mount()
    {
        //$this->fechaemision = Carbon::now();
        //$this->fechaemision = Carbon::now()->format('d m Y');
        $this->fechaemision = Carbon::now()->format('Y-m-d'); //Y-m-d es el formato en el cual se guardara en la BD, la vista mostrara dd/mm/yyy es por el navegadory la configuracion de la pc pero al escoger la fecha automaticamente lo convierte a Y-m-d y lo guarda en la BD

        $this->igv = Impuesto::where('siglas', 'IGV')->value('valor');//es el 18%
        $this->factoricbper = Impuesto::where('siglas', 'ICBPER')->value('valor');//es 0.2
        /* $this->moneda = Currency::where('default', 1)
        ->value('abbreviation'); */
        //$this->moneda = Company::where('id', $numero)->where('currency_id', );
        $this->currency_id = auth()->user()->employee->company->currency_id; //$this->currency_id  hace que en la lista de currencies muestre por defecto soles por ejemplo
        $this->moneda = auth()->user()->employee->company->currency->abbreviation;
        $this->company = auth()->user()->employee->company;

        $this->company_id = auth()->user()->employee->company->id;//compañia logueaada
        //$this->moneda = Currency::find($this->currency_id)->abbreviation;
        //$this->currency_id = $currency;
    }


    public function ScanCode($barcode,  $quantity = 1)
    {
        $this->search = $barcode;
        $company_id = auth()->user()->employee->company->id;
        //buscamos productos de la empresa
        $product = Product::where('company_id', $company_id)->where('codigobarras', $this->search)->first();
        if (!$product) {
            //session()->flash('alert', 'El producto no está registrado');
            //$this->msg = 'El producto no registrado';
            //dd($this->msg);
            //$this->emit('alert', $this->msg);
            //$title = 'El producto no está registrado';
        } elseif (!isset($product->saleprice)) {
            // $this->msg = 'El producto no tiene precio';
            // $this->emit('alert', $this->msg);
        } else {
            $this->addToCartbd(
                $product->id,
                $product->codigobarras,
                $product->name,
                $product->um->abbreviation,
                $product->tipoafectacion->codigo,
                $product->saleprice,
                $product->mtovalorgratuito,
                $product->mtovalorunitario,//precio del producto sin igv
                $product->esbolsa,
                $quantity
            );
            //$this->total = $this->getTotalFromTemporals();
            $this->getTotales();
            $this->getLegends();
            $this->getTotalFromTemporals();
        }
    }

    public function addToCartbd($product_id, $codigobarras, $name, $um, $tipafeigv, $saleprice, $mtovalorgratuito, $mtovalorunitario, $esbolsa, $quantity) //productId  captura al codigobarras
    {
        $company_id = auth()->user()->employee->company->id;
        //buscamos el producto en el carrito osea en la tabla temporal
        $productotemporal = Temporal::where('company_id', $company_id)->where('codigobarras', $codigobarras)->where('employee_id', auth()->user()->employee->id)->first();
        //dd($productotemporal);
        //si el producto existe actualizamos la cantidad
        if ($productotemporal) { //busca en el campo id de la coleccion
            $newQuantity = $productotemporal->quantity + $quantity;
            $newSubtotal = $newQuantity * $saleprice;

            $mtovalorunitario = $saleprice/(1 + ($this->igv*0.01));//actualizamos//precio de producto sin inc igv ejemplo 100
            $mtobaseigv = $newQuantity*$mtovalorunitario;//cantidad * precio sin igv
            $icbper = $esbolsa == 0 ? ($newQuantity * $this->factoricbper) : 0; //$quantity * $this->factoricbper  ejemplo 5*0.2
            $igv = $newSubtotal - ($newQuantity*$mtovalorunitario);//es 118 -100= 18soles
            $totalimpuestos = $icbper + $igv;
            $mtovalorventa = $mtovalorunitario * $newQuantity;//subtotal sin inc IGV ejemplo 100 x 2 = 200

            //$this->saleprice = $saleprice;
            //dd($quantity*$mtovalorunitario);
            $productotemporal->update([
                'quantity' => $newQuantity,
                'saleprice' => $saleprice,
                'mtovalorunitario' => $mtovalorunitario, //precio de producto sin inc igv ejemplo 100
                'subtotal' => $newSubtotal,//incluido igv
                'mtobaseigv' => $mtobaseigv,//cantidad * precio sin igv
                'igv' => $igv,//es 118 -100= 18soles
                'icbper' => $icbper,
                'totalimpuestos' => $totalimpuestos,
                'mtovalorventa' => $mtovalorventa,//subtotal sin inc IGV ejemplo 100 x 2 = 200
            ]);


        } else { //si el producto no esta en temporal lo creamos

            $subtotal = $quantity * $saleprice;
            $icbper = $esbolsa == 0 ? ($quantity * $this->factoricbper) : 0; //$quantity * $this->factoricbper  ejemplo 5*0.2
            $igv = $subtotal - ($quantity*$mtovalorunitario); //es 118 -100= 18soles
            Temporal::Create([
                'company_id' => $company_id,
                'employee_id' => auth()->user()->employee->id,
                'product_id' => $product_id,
                'codigobarras' => $codigobarras,
                'name' => $name,
                'um' => $um,
                'tipafeigv' => $tipafeigv,//esta en la tabla productos y no cambia, toma valores de 10 Gravado - Operación Onerosa tabla tipo afectacion del igv
                'saleprice' => $saleprice,//es precio de producto incluido igv  ejemplo 118, mtoPrecioUnitario
                'mtovalorgratuito' => $mtovalorgratuito,
                'mtovalorunitario' => $mtovalorunitario, //precio de producto sin inc igv ejemplo 100
                'mtobaseigv' => $quantity*$mtovalorunitario,//cantidad * precio sin igv
                'quantity' => $quantity,
                'porcentajeigv' => $this->igv,  //igv lo tenemos en el mount es 18%
                'subtotal' => $subtotal,
                'igv' => $igv, //ejemplo es es 118 -100= 18soles
                'factoricbper' => $this->factoricbper,  //factoricbper lo tenemos en el mount es 0.2
                //'icbper' => $quantity * $this->factoricbper,
                'icbper' => $icbper,
                'totalimpuestos' => $icbper + $igv,
                'mtovalorventa' => $mtovalorunitario * $quantity,//subtotal sin inc IGV ejemplo 100 x 2 = 200
                'esbolsa' => $esbolsa,

                // 'subtotal' => $saleprice*1,
                //'image' => $image,
            ]);

            //$this->getTotales();
        }
    }


    public function getTotales()
    {

        $this->mtoopergravadas = Temporal::where('company_id', $this->company_id)
        ->where('employee_id', auth()->user()->employee->id)
        ->where('tipafeigv', '10')
        ->sum('mtovalorventa');


        $this->mtooperexoneradas = Temporal::where('company_id', $this->company_id)
        ->where('employee_id', auth()->user()->employee->id)
        ->where('tipafeigv', '20')
        ->sum('mtovalorventa');

        $this->mtooperinafectas =  Temporal::where('company_id', $this->company_id)
        ->where('employee_id', auth()->user()->employee->id)
        ->where('tipafeigv', '30')
        ->sum('mtovalorventa');

        $this->mtooperexportacion =  Temporal::where('company_id', $this->company_id)
        ->where('employee_id', auth()->user()->employee->id)
        ->where('tipafeigv', '40')
        ->sum('mtovalorventa');

        $this->mtoopergratuitas =  Temporal::where('company_id', $this->company_id)
        ->where('employee_id', auth()->user()->employee->id)
        ->whereNotIn('tipafeigv', ['10', '20', '30', '40'])
        ->sum('mtovalorventa');

        $this->mtoigv =  Temporal::where('company_id', $this->company_id)//es la suma de todos los igv
        ->where('employee_id', auth()->user()->employee->id)
        ->whereIn('tipafeigv', ['10', '20', '30', '40'])
        ->sum('igv');

        $this->mtoigvgratuitas =  Temporal::where('company_id', $this->company_id)
        ->where('employee_id', auth()->user()->employee->id)
        ->whereNotIn('tipafeigv', ['10', '20', '30', '40'])
        ->sum('igv');



        $this->icbper =  Temporal::where('company_id', $this->company_id)
        ->where('employee_id', auth()->user()->employee->id)
        ->where('esbolsa',1)
        ->sum('icbper');

        $this->totalimpuestos = number_format($this->mtoigv + $this->icbper, 4);//formatea a 4 decimales si tiene 6 dcimales solo muestra 4 pero no redondea para redondear debemos usar round

        $this->valorventa =  Temporal::where('company_id', $this->company_id) //es el total sin inc igv ejemplo 100x2= 200 soles
        ->where('employee_id', auth()->user()->employee->id)
        ->whereIn('tipafeigv', ['10', '20', '30', '40'])
        ->sum('mtovalorventa');

        $this->subtotal = number_format($this->valorventa + $this->totalimpuestos, 4);

        //$this->mtoimpventa = floor($this->subtotall * 10) / 10;
        $this->mtoimpventa = $this->subtotall;

        $this->redondeo = $this->subtotall - $this->mtoimpventa;


    }

    public function getLegends()
    {
        $formatter = new NumeroALetras();

        $this->legends = [];

        $this->legends[] = [
            'code' => '1000',
            'value' => $formatter->toInvoice($this->mtoimpventa, 4)
        ];

        /* if (collect($this->invoice['details'])->whereNotIn('tipAfeIgv', ['10', '20', '30', '40'])->count()) {
            $legends[] = [
                'code' => '1002',
                'value' => 'TRANSFERENCIA GRATUITA DE UN BIEN Y/O SERVICIO PRESTADO GRATUITAMENTE'
            ];
        }

        if ($this->invoice['tipoOperacion'] == '1001') {
            $legends[] = [
                'code' => '2006',
                'value' => 'Operación sujeta a detracción'
            ];
        } */

       // $this->invoice['legends'] = $legends;
       return $this->legends;
    }



    public function getTotalFromTemporals()
    {
        $formatter = new NumeroALetras();
        //cambiar por this->company_id
        $company_id = auth()->user()->employee->company->id;

        // Obtener el total de la tabla temporals para la empresa actual
        $this->total = Temporal::where('company_id', $company_id)
            ->where('employee_id', auth()->user()->employee->id)
            ->sum('subtotal');


        //$this->totalenletras = $formatter->toInvoice($this->subtotall, 4);
        //$this->totalenletras = $formatter->toInvoice($this->mtoimpventa, 2);
        $this->totalenletras = $formatter->toInvoice($this->total, 2);

        return $this->total;//esto controla la vista del carrito
        //return $this->totalenletras;
    }



    // actualizar cantidad item en carrito
    public function updateQty($id, $saleprice, $quantity = 1, $mtovalorunitario)
    {
        if ($quantity <= 0){
            $this->removeItem($id);}
        else{
            $this->updateQuantity($id, $saleprice, $quantity, $mtovalorunitario);

        }
    }


    //pasaremos parametros desde la vista
    public function updateQuantity($id, $saleprice, $quantity, $mtovalorunitario)
    {
        $subtotal = $quantity * $saleprice;
        ////////////////////////////////////////
        //// debo actualizar en temporal  las variables que usan cantidades /////
        /////////////////////////////////////

        if ($saleprice > 0 and $quantity > 0) {
            $productelegido = Temporal::where('id', $id)->first();
            $productelegido->update([
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'mtobaseigv' => $quantity*$mtovalorunitario,//cantidad * precio sin igv
                'igv' => $subtotal - ($quantity*$mtovalorunitario),//es 118 -100= 18soles
                'icbper' => $quantity * $this->factoricbper,
                'totalimpuestos' => $quantity * $this->factoricbper + ($subtotal - ($quantity*$mtovalorunitario)),
                'mtovalorventa' => $mtovalorunitario * $quantity,//subtotal sin inc IGV ejemplo 100 x 2 = 200

            ]);
        }

        $this->total = $this->getTotalFromTemporals();
        //agregue esto para actualizar parametros
        $this->getTotales();
    }



    public function delete(Temporal $temporal)//elimina todo
    {
        //$this->authorize('update', $brand);

        $temporal->delete();
        $this->total = $this->getTotalFromTemporals();
        //agregue esto para actualizar parametros
        $this->getTotales();
        //debemos limiar el array invoive
    }


    public function removeItem($id)
    {
        $registro = Temporal::find($id);
        if ($registro) {
            // Si se encontró el modelo, eliminarlo
            $registro->delete();
            $this->total = $this->getTotalFromTemporals();
        }
    }

    public function updatePrice($id, $saleprice, $quantity)
    {
        $subtotal = $quantity * $saleprice;
        $mtovalorunitario = $saleprice/(1 + ($this->igv*0.01));//actualizamos//precio de producto sin inc igv ejemplo 100
        $mtobaseigv = $quantity*$mtovalorunitario;//cantidad * precio sin igv
        $igv = $subtotal - ($quantity*$mtovalorunitario);//es 118 -100= 18soles
        $totalimpuestos = $quantity * $this->factoricbper + ($subtotal - ($quantity*$mtovalorunitario));
        $mtovalorventa = $mtovalorunitario * $quantity;//subtotal sin inc IGV ejemplo 100 x 2 = 200

        //se cambio el precio, cambiara precio sin igv, el igv y el subtotal
        if ($saleprice > 0 and $quantity > 0) {
            $productelegido = Temporal::where('id', $id)->first();
            $productelegido->update([
                'saleprice' => $saleprice,
                'subtotal' => $subtotal,
                'mtovalorunitario' => $mtovalorunitario,
                'mtobaseigv' => $mtobaseigv,
                'igv' => $igv,
                'totalimpuestos' => $totalimpuestos,
                'mtovalorventa' => $mtovalorventa,//subtotal sin inc IGV ejemplo 100 x 2 = 200
            ]);
        }


        $this->total = $this->getTotalFromTemporals();
        //agregue esto para actualizar parametros
        $this->getTotales();
    }

    public function limpiar()
    {
        Temporal::where('company_id', auth()->user()->employee->company->id)
            ->where('employee_id', auth()->user()->employee->id)
            ->delete();
    }


    public function updatedCurrencyId($value)
    {
        // $this->moneda = Currency::where('id', $value);
        $this->moneda = Currency::where('id', $value)->value('abbreviation');
    }

    //tipocomprobante_id

    public function updatedTipocomprobanteId($value)
    {
        $tipocomprobantes = auth()->user()->employee->local->tipocomprobantes;
        // Encuentra el tipo de comprobante seleccionado
        $selectedTipoComprobante = $tipocomprobantes->where('id', $value)->first();
        // Actualiza el valor de tipoDoc en tu array de invoice
        $this->tipodocumento_id = $selectedTipoComprobante->id;//aqui da el valor de tipocomprobante factura , boleta para enviara a sunat
        //dd($selectedTipoComprobante->codigo);
        // $this->moneda = Currency::where('id', $value);
        //$this->serie = Currency::where('id', $value)->value('abbreviation');
        // Lógica para actualizar el campo "Serie" cuando cambia el comprobante seleccionado
        // Puedes personalizar esta lógica según tus necesidades

        $local = auth()->user()->employee->local;

        //dd($this->tc = $local->tipocomprobantes);  se obtiene la tabla tipocomprobantes pero que son de local logueado
        // Obtener la serie a través de la relación muchos a muchos
        $this->serie = $local->tipocomprobantes
            ->where('id', $value)
            ->first()
            ->pivot
            ->serie ?? "null";

        //$this->invoice['serie'] = $this->serie;
        //dd($this->invoice['serie']);

        $company_id = auth()->user()->employee->company->id;
        switch ($value) {
            case 1://para factura

                /* $lastBoleta = Boleta::where('company_id', $company_id)
                    ->where('serie', $this->serie)
                    ->latest('numero')
                    ->first();

                if ($lastBoleta) {
                    $this->numero = $lastBoleta->numero;
                }

                $this->numero = 1;
                break; */
                $this->numero = 8;
                break;


            case 2:
                $lastBoleta = Boleta::where('company_id', $company_id)
                    ->where('serie', $this->serie)
                    ->latest('numero')
                    ->first();

                $this->numero = $lastBoleta ? $lastBoleta->numero : 81;
                break;

            case 3:
                $this->numero = 300;
                break;
            default:
                // Manejo por defecto si $value no coincide con ninguno de los casos anteriores
                break;
        }
    }

    protected $rules = [
        'tipodocumento_id' => 'required',
        'customer_id' => 'required',
        'tipocomprobante_id' => 'required',
        'paymenttype_id' => 'required',
        'fechaemision' => 'required|date',
        //'fechavencimiento' => 'required|date|before_or_equal:fechaemision',
        'fechavencimiento' => 'required|date|after_or_equal:fechaemision',
        'currency_id' => 'required',
        'serie' => 'required',
        'numero' => 'required',
    ];






    //guardamos el comprobante
    public function save()
    {

        $this->validate();
        //el cliente esta en la tabla customer
        //buscamos el cliente y el tipo documento ruc, dni, carnet de extranjeria, etc
        $customer = Customer::find($this->customer_id);
        $tipodocumento = Tipodocumento::find($this->tipodocumento_id);//ruc , dni
        //dd($tipodocumento->abbreviation);
        //dd($this->invoice['company']['address']['codLocal']);
        // Validación de que la fecha de vencimiento sea mayor o igual a la fecha de emisión
        $this->local_id = auth()->user()->employee->local->id;
        //factura. boleta
        $this->local_tipocomprobante_id = Local_tipocomprobante::where('local_id', $this->local_id)->where('tipocomprobante_id', $this->tipocomprobante_id)->value('id');


        $this->serienumero = $this->serie . "-" . $this->numero;
        //dd($this->serienumero);


        //guadamos la tabla comprobantes
        $comprobante = Comprobante::create([
            'customer_id' => $this->customer_id,
            'local_id' => $this->local_id,
            'tipocomprobante_id' => $this->tipocomprobante_id,//factura boleta
            'local_tipocomprobante_id' => $this->local_tipocomprobante_id,
            'company_id' => auth()->user()->employee->company->id, //encontramos la company actual osea la compania del usuario logueado
            'employee_id' => auth()->user()->employee->id,
            //guardaremos campos para facturacion electronica
            'tipodeoperacion_id' => 1,//venta interna en este caso ponemos 0101
            'tipodocumento_id' => $this->tipodocumento_id,//ruc, dni
            'fechaemision' =>  $this->fechaemision,
            'fechavencimiento' =>  $this->fechavencimiento,
            'paymenttype_id' => $this->paymenttype_id,//contado, credito
            'currency_id' => $this->currency_id,//PEN, USD
            'mtoopergravadas' => $this->mtoopergravadas,
            'mtooperexoneradas' => $this->mtooperexoneradas,
            'mtooperinafectas' => $this->mtooperinafectas,
            'mtooperexportacion' => $this->mtooperexportacion,
            'mtoopergratuitas' => $this->mtoopergratuitas,
            'mtoigv' => $this->mtoigv,
            'mtoigvgratuitas' => $this->mtoigvgratuitas,
            'icbper' => $this->icbper,
            'totalimpuestos' => $this->totalimpuestos,
            'valorventa' => $this->valorventa,
            'subtotal' => $this->subtotall,
            'mtoimpventa' => $this->mtoimpventa,
            'redondeo' => $this->redondeo,
            'legends' => json_encode($this->getLegends()),
            //'legends' => json_encode($this->legends),
            //anticipos
            //detracciones
            'nota' => $this->nota,

        ]);

        //guadamos la tabla boletas
        $boleta = Boleta::create([
            'serie' => $this->serie,
            'numero' => $this->numero,
            'serienumero' => $this->serienumero,
            'fechaemision' =>  $this->fechaemision,
            'fechavencimiento' => $this->fechavencimiento,
            'total' => $this->total,
            'comprobante_id' => $comprobante->id,
            'company_id' => auth()->user()->employee->company->id,
            'paymenttype_id' => $this->paymenttype_id,
            'currency_id' => $this->currency_id,
            'tipodecambio_id' => 1,//1 es un codigo de la tabla tipo de cambios es el id

            //guardaremos campos para facturacion electronica

        ]);

        //guadamos la tabla comprobante_product
        //lo comente porque accede muchas veces a la BD
        $temporals = Temporal::where('company_id', auth()->user()->employee->company->id)
            ->where('employee_id', auth()->user()->employee->id)->get();

        foreach ($temporals as $temporal) {
            //si el producto es bolsa agregar icbper de lo contrario no///////////////////////////////
            //$this->invoice['details'][] = $this->item;

                 Comprobante_Product::create([
                'cant' => $temporal->quantity,
                'price' => $temporal->saleprice,
                'subtotal' => $temporal->subtotal,
                'product_id' => $temporal->product_id,
                'comprobante_id' => $comprobante->id,
                'codigobarras' => $temporal->codigobarras, //codigo del producto que necesita la facturacion electronica
                'mtobaseigv' => $temporal->mtobaseigv,
                'igv' => $temporal->igv,
                'icbper' => $temporal->icbper,
                'totalimpuestos' => $temporal->totalimpuestos,
                'mtovalorventa' => $temporal->mtovalorventa,


            ]);
        }

        //$this->getTotales();
        //$this->getLegends();



        //guadamos la tabla comprobante_product

        /* $temporals = Temporal::where('company_id', auth()->user()->employee->company->id)
            ->where('employee_id', auth()->user()->employee->id)
            ->get();

        $comprobanteProductData = $temporals->map(function ($temporal) use ($comprobante) {
            return [
                'cant' => $temporal->quantity,
                'price' => $temporal->saleprice,
                'subtotal' => $temporal->subtotal,
                'product_id' => $temporal->product_id,
                'comprobante_id' => $comprobante->id,
            ];
        });

        Comprobante_Product::insert($comprobanteProductData->toArray());

        $temporals->each->delete(); */


        //facturacion electronica
        $sunat = new SunatService($comprobante, $this->company, $temporals, $boleta);

        $sunat->getSee();
        $sunat->setInvoice();
        $sunat->send();
        $sunat->generatePdfReport();

        $temporals->each->delete();

        /* $xml = $this->see->getFactory()->getLastXml();
        $this->invoice['xml'] = $this->see->getFactory()->getLastXml();
        $this->invoice['hash'] = (new XmlUtils())->getHashSign($xml);

       dd($this->invoice); */


        //$this->emitTo('admin.comprobante-list', 'render');

        $this->emit('alert', 'El comprobante se creo correctamente');

        return redirect()->route('admin.comprobante.list');






        //eliminar el temporal


        //enviar mensaje de guardado


    }


    /*  public function fechaemision($selectedDate)
    {
        $this->fechaemision = $selectedDate;
    } */

    public function render()
    {
        //$this->mtoimpventa

        $company_id = auth()->user()->employee->company->id;

        $cart = Temporal::where('company_id', $company_id)->where('employee_id', auth()->user()->employee->id)->get();

        //dd($cart);

        $customers = Customer::all();
        $currencies = Currency::all();
        $tipodocumentos = Tipodocumento::all();


        $tipocomprobantes = auth()->user()->employee->local->tipocomprobantes;
        //dd($tipocomprobantes);
        $this->getTotales();
        $this->total = $this->getTotalFromTemporals();



        return view('livewire.admin.comprobante-create', compact('customers', 'currencies', 'tipocomprobantes', 'cart', 'tipodocumentos'));
    }
}
