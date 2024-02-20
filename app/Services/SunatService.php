<?php

namespace App\Services;

use DateTime;
use Greenter\See; //importar
use Greenter\Model\Sale\Note;
use Greenter\Report\XmlUtils;
use Greenter\Report\PdfReport;
use Greenter\Model\Sale\Legend;
use Greenter\Report\HtmlReport;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Despatch\Driver;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Despatch\Vehicle;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Direction;
use Illuminate\Support\Facades\Storage;
use App\Models\Company as ModelsCompany;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\XMLSecLibs\Sunat\SignedXml;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Report\Resolver\DefaultTemplateResolver;


class SunatService
{
    public $comprobante, $company, $temporals, $boleta;
    public $see;
    public $voucher;
    public $result;


    public function __construct($comprobante, $company, $temporals, $boleta)
    {
        $this->comprobante = $comprobante;
        $this->company = $company;
        $this->temporals = $temporals;
        $this->boleta = $boleta;
    }

    public function getSee()
    {
        // configuraremos el certificado digital, la ruta del servicio y las credenciales (Clave SOL) a utilizar:
        /* $see = new See();
        $see->setCertificate(Storage::get($this->company->certificate_path)); //le pasamos la ruta del certificado, da como resultado el contenido del certificado
        $see->setService($this->company->production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA); //le indicamos si es beta o produccion
        $see->setClaveSOL($this->company->ruc, $this->company->soluser, $this->company->solpass); //le pasamos los datos de la clave sol usurio secundario
        return $see; //retornamos todos los valores */
        $endpoint = $this->company->production ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA;

        $this->see = new See();
        $this->see->setCertificate(Storage::get("certificates/certificate_1.pem"));
        $this->see->setService($endpoint);
        $this->see->setClaveSOL($this->company->ruc, $this->company->soluser, $this->company->solpass);
    }

    public function setInvoice()
    {
        $this->voucher = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion($this->comprobante->tipodeoperacion->codigo) // Venta - Catalog. 51
            ->setTipoDoc($this->comprobante->tipocomprobante->codigo) // Factura - Catalog. 01, factura 01, boleta 03

            ->setSerie($this->boleta->serie)
            ->setCorrelativo($this->boleta->numero) // Zona horaria: Lima
            //->setFechaEmision($this->invoice['fechaEmision']) // Zona horaria: Lima
            ->setFechaEmision(new \DateTime($this->boleta->fechaEmision))
            ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
            ->setTipoMoneda($this->boleta->currency->name) // Sol - Catalog. 02
            ->setCompany($this->getCompany())
            ->setClient($this->getClient())

            //MtoOper
            ->setMtoOperGravadas($this->comprobante->mtoopergravadas)
            ->setMtoOperExoneradas($this->comprobante->mtooperexoneradas)
            ->setMtoOperInafectas($this->comprobante->mtooperinafectas)
            ->setMtoOperExportacion($this->comprobante->mtooperexportacion)
            ->setMtoOperGratuitas($this->comprobante->mtoopergratuitas)

            //Impuestos
            ->setMtoIGV($this->comprobante->mtoigv) //todo los igv
            ->setMtoIGVGratuitas($this->comprobante->mtoigvgratuitas)
            ->setIcbper($this->comprobante->icbper)
            ->setTotalImpuestos($this->comprobante->totalimpuestos)

            //Totales
            ->setValorVenta($this->comprobante->valorventa)
            ->setSubTotal($this->comprobante->subtotal)
            ->setRedondeo($this->comprobante->redondeo)
            ->setMtoImpVenta($this->comprobante->mtoimpventa)

            //Productos
            ->setDetails($this->getSaleDetails())

            //Leyendas
            ->setLegends($this->getLegends());
    }

    public function getClient()
    {
        return (new Client())
            ->setTipoDoc($this->comprobante->tipodocumento->codigo)
            ->setNumDoc($this->comprobante->customer->numdoc)
            ->setRznSocial($this->comprobante->customer->nomrazonsocial)
            ->setAddress(
                (new Address())
                    ->setDireccion($this->comprobante->customer->address)
            );
    }

    public function getCompany()
    {
        return (new Company())
            ->setRuc($this->company->ruc)
            ->setRazonSocial($this->company->razonsocial)
            ->setNombreComercial($this->company->nombrecomercial)
            ->setAddress(
                (new Address())
                    ->setUbigueo($this->company->ubigeo)
                    ->setDepartamento("Lima")
                    ->setProvincia("Lima")
                    ->setDistrito("Lima")
                    ->setUrbanizacion($this->company->urbanizacion)
                    ->setDireccion($this->company->direccion)
            );
    }

    public function getSaleDetails()
    {
        $details = [];

        foreach ($this->temporals as $item) {

            $details[] = (new SaleDetail())
                ->setCodProducto($item->codigobarras)
                ->setUnidad($item->um)
                ->setDescripcion($item->name)
                ->setCantidad($item->quantity)
                ->setMtoValorGratuito($item->mtovalorgratuito)
                ->setMtoValorUnitario($item->mtovalorunitario) //precio unitario sin igv
                ->setMtoBaseIgv($item->mtobaseigv) // precio unitario sin igv * cantidad
                ->setPorcentajeIgv($item->porcentajeigv) //18%
                ->setIgv($item->igv) //igv por item
                ->setFactorIcbper($item->factoricbper) //como el igv es 18% , aqui es 0.2
                ->setIcbper($item->icbper) //cantidad * factoricbper
                ->setTipAfeIgv($item->tipafeigv)
                ->setTotalImpuestos($item->totalimpuestos)
                //->setTotalImpuestos($item->igv)//esto esta monentaneo
                ->setMtoValorVenta($item->mtovalorventa) //cantidad * precio unitario sin igv
                ->setMtoPrecioUnitario($item->saleprice); //mtoPrecioUnitario es el sale price
        }

        return $details;
    }


    public function getLegends()
    {
        $legends = [];

        // Decodificar el JSON para obtener un array asociativo
        $legendsArray = json_decode($this->comprobante->legends, true);

        if ($legendsArray !== null) {
            foreach ($legendsArray as $legend) {
                // Crear objetos Legend y agregarlos al array
                $legends[] = (new Legend())
                    ->setCode($legend['code']) // Catalog. 52
                    ->setValue($legend['value']);
            }
        }

        return $legends;
    }


    //Enviar a Sunat
    public function send()
    {

        $this->result = $this->see->send($this->voucher);
        $this->boleta->send_xml = true;
        $this->boleta->sunat_success = $this->result->isSuccess();
        $this->boleta->save();
        //dd($this->boleta);
        // Guardar XML firmado digitalmente.
        $xml = $this->see->getFactory()->getLastXml();
        $this->boleta->hash = (new XmlUtils())->getHashSign($xml);
        $this->boleta->xml_path = 'invoices/xml/' . $this->voucher->getName() . '.xml';
        Storage::put($this->boleta->xml_path, $xml, 'public');


        // Verificamos que la conexión con SUNAT fue exitosa.
        if (!$this->boleta->sunat_success) {

            $this->boleta->sunat_error = [
                'code' => $this->result->getError()->getCode(),
                'message' => $this->result->getError()->getMessage()
            ];
            $this->boleta->save();

            /*  session()->flash('flash.sweetAlert', [
                'icon' => 'error',
                'title' => 'Codigo Error: ' . $this->boleta->sunat_error['code'],
                'text' => $this->boleta->sunat_error['message']
            ]); */

            return;
        }



        // Guardamos el CDR
        $this->boleta->sunat_cdr_path = "invoices/cdr/R-{$this->voucher->getName()}.zip";
        Storage::put($this->boleta->sunat_cdr_path, $this->result->getCdrZip(), 'public');
        $this->boleta->save();

        //Lectura del CDR
        $this->readCdr();
    }


    //Lectura del CDR
    public function readCdr()
    {
        $cdr = $this->result->getCdrResponse();

        $this->boleta->cdr_code = (int)$cdr->getCode();
        $this->boleta->cdr_notes = count($cdr->getNotes()) ? $cdr->getNotes() : null;
        $this->boleta->cdr_description = $cdr->getDescription();

        $this->boleta->save();

        if ($this->boleta->cdr_code === 0) {

            /*  session()->flash('flash.sweetAlert', [
                'icon' => 'success',
                'title' => 'ESTADO: ACEPTADA',
                'text' => $this->boleta->cdr_notes ? 'OBSERVACIONES: ' . implode(', ', $cdr->getNotes()) : null,
                'footer' => $this->boleta->cdr_description,
            ]); */
        } else if ($this->boleta->cdr_code >= 2000 && $this->boleta->cdr_code <= 3999) {

            /* session()->flash('flash.sweetAlert', [
                'icon' => 'error',
                'title' => 'ESTADO: RECHAZADA',
                'footer' => $this->boleta->cdr_description,
            ]); */
        } else {
            /* Esto no debería darse, pero si ocurre, es un CDR inválido que debería tratarse como un error-excepción. */
            /*code: 0100 a 1999 */
            /*  session()->flash('flash.sweetAlert', [
                'icon' => 'error',
                'title' => 'Excepción',
                'footer' => $this->boleta->cdr_description,
            ]); */
        }
    }



    public function generatePdfReport()
    {

        $htmlReport = new HtmlReport(resource_path('views/sunat/template'), ['strict_variables' => true]);
        $htmlReport->setTemplate((new DefaultTemplateResolver())->getTemplate($this->voucher));

        $report = new PdfReport($htmlReport);
        $report->setOptions([
            'no-outline',
            'viewport-size' => '1280x1024',
            'page-width' => '21cm',
            'page-height' => '29.7cm',
        ]);
        $report->setBinPath(env('WKHTMLTOPDF_PATH')); // Ruta relativa o absoluta de wkhtmltopdf

        $params = [
            'system' => [
                'logo' => $this->company->rectangle_image_path ? Storage::get($this->company->rectangle_image_path) : file_get_contents('img/logos/logo.png'), // Logo de Empresa
                'hash' => $this->boleta->hash, // Valor Resumen
            ],
            'user' => [
                'header'     => "Telf: <b>{$this->company->phone}</b>", // Texto que se ubica debajo de la dirección de empresa
                'extras'     => [
                    // Leyendas adicionales
                    ['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'],
                    ['name' => 'VENDEDOR', 'value' => 'GITHUB SELLER'],
                ],
                'footer' => '<p>Nro Resolucion: <b>3232323</b></p>'
            ]
        ];

        $pdf = $report->render($this->voucher, $params);

        if ($pdf) {
            $this->boleta->pdf_path = 'invoices/pdf/' . $this->voucher->getName() . '.pdf';
            Storage::put($this->boleta->pdf_path, $pdf, 'public');

            $this->boleta->save();
        }
    }
}
