<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Boleta</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            width: 7.5cm;
            /* Establece el ancho máximo del cuerpo del documento */
            margin: 0 auto;
            /* Centra el contenido horizontalmente */
            background-color: white;
            /* Establece el color de fondo blanco */
            padding: 10px;
            /* Añade un relleno alrededor del contenido para separarlo del borde */
        }

        img {
            max-width: 100%;
            height: auto;
        }

        h1,
        p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table,
        th,
        td {
            border: 0px solid black;
            padding: 5px;
        }
    </style>
</head>

<body>
    <div>
        {{-- <img src="images/ticom.jpg" alt="Logo Empresa"> --}}
        <h1>TICOM SRL</h1>
        <p>RUC: 20447393302</p>
        <p>Av. Petith Thouars 1255 Of. 302 Lima-Lima-Lima</p>

        <h4>BOLETA ELECTRÓNICA</h4>
        <p>B001 - 000487</p>
        <p>Fecha: 16/02/2024</p>
        <hr>
        <p>RAZ. SOC. : BTEC PERU SRL</p>
        <p>DIRECCIÓN : Av. cuba 1256 Jesús María, Lima, Lima</p>
        <p>FORMA PAGO: CONTADO</p>
        <hr>
        <!-- Otros datos del comprobante -->
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>

                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>----------</td>
                    <td>----------</td>
                    <td>----------</td>
                    <td>----------</td>
                </tr>
                <!-- Detalles de los productos -->
                <tr>
                    <td>Producto 1</td>
                    <td>1</td>
                    <td>$10.00</td>
                    <td>$10.00</td>
                </tr>

                <tr>
                    <td>Producto 2</td>
                    <td>2</td>
                    <td>$15.00</td>
                    <td>$30.00</td>
                </tr>

                <tr>
                    <td></td>
                    <td></td>
                    <td>OP gravadas:</td>
                    <td> $36.00</td>
                </tr>

                <tr>
                    <td></td>
                    <td></td>
                    <td>IGV:</td>
                    <td> $4.00</td>
                </tr>

                <tr>
                    <td></td>
                    <td></td>
                    <td>Total:</td>
                    <td> $40.00</td>
                </tr>
            </tbody>
        </table>
        <!-- Totales en números y letras -->
        <p> </p>
        <p>Cuarenta  con 00/100  dólares</p>
        <hr>
        <p>aqui va el codigo QR</p>
        <hr>
        <p>ghfgf43fdgfdqa2gfhgfhgfh=-+gfhgf</p>
        <P>Representación electrónica de la boleta electrónica</P>
        <p>Este documento puede ser consultado en www.ticomperu.com</p>
        <p>Autorizado medinte resolución de intendencia Nro 034.005-0007633/SUNAT</p>
    </div>
</body>

</html>
