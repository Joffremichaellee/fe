<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Punto de Venta') }}
        </h2>


    </x-slot>

    <div class="grid px-4 mx-auto mt-4 max-w-7xl sm:px-6 lg:px-8">

        <div class="px-3 bg-white">

            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div>


                        <form wire:submit.prevent="store">
                            <div class="space-y-4">
                                
                        
                                
                        
                                {{-- <x-jet-input 
                                    wire:model.defer="company.ruc"
                                    label="RUC"
                                    placeholder="Ingrese el RUC de la empresa" /> --}}
                                <x-jet-input
                                    wire:model.defer="ruc"
                                    label="RUC"
                                    placeholder="Ingrese el RUC de la empresa"
                                    type="text"
                                    maxlength="11">
                                    <x-slot name="append">
                                        <div class="absolute inset-y-0 right-0 flex items-center p-0.5">
                                            
                                        </div>
                                    </x-slot>
                                </x-jet-input>
                                <x-jet-secondary-button class="ml-2 mr-2" wire:click="searchRuc">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </x-jet-secondary-button>
                        
                                <x-jet-input 
                                    wire:model.defer="razonsocial"
                                    label="Razon social"
                                    placeholder="Ingrese la razón social de la empresa" />
                        
                                
                        
                        
                                
                              
                        
                                <div class="flex justify-end">
                                    <x-jet-button type="submit" dark>
                                        Guardar
                                    </x-jet-button>
                                </div>
                        
                                {{-- <pre><code>{{ json_encode($company, JSON_PRETTY_PRINT) }}</code></pre> --}}
                            </div>
                        </form>
                            

                            


                        <hr class="mt-5 mb-5">

                        {{-- aqui ira los productos a vender --}}



                        <div class="flex mt-4">
                            <input type="text" id="code" class="block w-full bg-gray-100"
                                wire:keydown.enter.prevent="ScanCode($('#code').val())" />

                            <x-jet-secondary-button class="ml-2 mr-2" wire:click="limpiar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </x-jet-secondary-button>

                            <a class="btn btn-red" wire:click="$emit('limpiarTemporal')">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>


                        </div>


                        {{-- @if (session()->has('alert'))
                                    <p class="mt-2 text-red-500">{{ session('alert') }}</p>
                                    {{ session()->forget('alert') }}
                                @endif --}}


                        {{-- {{ $cart }} --}}

                        <section class="w-full px-4 mt-4 antialiased text-gray-600">
                            <div class="flex flex-col justify-center w-full h-full">
                                <!-- Table -->
                                @if ($total > 0)
                                    <div class="w-full mx-auto bg-white border border-gray-200 rounded-sm shadow-lg">
                                        <div class="p-3">
                                            <div class="overflow-x-auto">
                                                <table class="w-full table-auto">
                                                    <thead
                                                        class="text-xs font-semibold text-gray-400 uppercase bg-gray-50">
                                                        <tr>
                                                            <th class="p-2 whitespace-nowrap">
                                                                <div class="font-semibold text-left">Imagén
                                                                </div>
                                                            </th>
                                                            <th class="p-2 whitespace-nowrap">
                                                                <div class="font-semibold text-left">Código
                                                                </div>
                                                            </th>
                                                            <th class="p-2 whitespace-nowrap">
                                                                <div class="font-semibold text-left">Nombre
                                                                </div>
                                                            </th>
                                                            <th class="p-2 whitespace-nowrap">
                                                                <div class="font-semibold text-center">precio
                                                                </div>
                                                            </th>
                                                            <th class="p-2 whitespace-nowrap">
                                                                <div class="font-semibold text-center">cantidad
                                                                </div>
                                                            </th>
                                                            <th class="p-2 whitespace-nowrap">
                                                                <div class="font-semibold text-center">Subtotal
                                                                </div>
                                                            </th>
                                                            <th class="p-2 whitespace-nowrap">
                                                                <div class="font-semibold text-center">Acciones
                                                                </div>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="text-sm divide-y divide-gray-100">
                                                        @foreach ($cart as $item)
                                                            <tr>
                                                                <td class="p-2 whitespace-nowrap">
                                                                    <div class="flex items-center">
                                                                        <div
                                                                            class="flex-shrink-0 w-10 h-10 mr-2 sm:mr-3">
                                                                            <img class="rounded-full" src="#"
                                                                                width="40" height="40">
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="p-2 whitespace-nowrap">
                                                                    <div class="text-left">
                                                                        {{ $item->codigobarras }}
                                                                    </div>
                                                                </td>
                                                                <td class="p-2 whitespace-nowrap">
                                                                    <div class="font-medium text-left text-green-500">
                                                                        {{ $item->name }} </div>
                                                                </td>
                                                                <td class="p-2 whitespace-nowrap">
                                                                    {{-- <div class="text-center">${{number_format($item->saleprice,2)}}</div> --}}
                                                                    <div class="w-20 text-lg text-center">
                                                                        <input type="text"
                                                                            id="p{{ $item->id }}"
                                                                            wire:change="updatePrice('{{ $item->id }}', $('#p' + '{{ $item->id }}').val(), $('#r' + '{{ $item->id }}').val())"
                                                                            style="font-size: 1rem!important"
                                                                            class="w-20 text-center form-control"
                                                                            value="{{ number_format($item->saleprice, 4) }}">
                                                                    </div>
                                                                </td>

                                                                <td class="p-2 whitespace-nowrap">
                                                                    <div class="w-20 text-lg text-center">
                                                                        <input type="number"
                                                                            id="r{{ $item->id }}"
                                                                            wire:change="updateQty('{{ $item->id }}', $('#p' + '{{ $item->id }}').val(), $('#r' + '{{ $item->id }}').val(), '{{ $item->mtovalorunitario }}')"
                                                                            style="font-size: 1rem!important"
                                                                            class="w-20 text-center form-control"
                                                                            value="{{ $item->quantity }}">
                                                                    </div>
                                                                </td>



                                                                <td class="p-2 whitespace-nowrap">
                                                                    <div class="text-lg text-right">
                                                                        {{ number_format($item->saleprice * $item->quantity, 4) }}
                                                                    </div>
                                                                </td>

                                                                <td class="p-2 whitespace-nowrap">


                                                                    <a class="btn btn-red"
                                                                        wire:click="$emit('deleteTemporal', {{ $item->id }})">
                                                                        <i class="fa-solid fa-trash-can"></i>
                                                                    </a>
                                                                </td>

                                                            </tr>
                                                        @endforeach
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="p-2 whitespace-nowrap">
                                                                <div class="text-lg text-right">
                                                                   SUB-TOTAL
                                                                </div>
                                                            </td>
                                                            <td class="p-2 whitespace-nowrap">
                                                                <div class="text-lg text-right">
                                                                    {{ $valorventa }}
                                                                </div>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="p-2 whitespace-nowrap">
                                                                <div class="text-lg text-right">
                                                                   ICBPER
                                                                </div>
                                                            </td>
                                                            <td class="p-2 whitespace-nowrap">
                                                                <div class="text-lg text-right">
                                                                    {{ $icbper }}
                                                                </div>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="p-2 whitespace-nowrap">
                                                                <div class="text-lg text-right">
                                                                   IGV
                                                                </div>
                                                            </td>
                                                            <td class="p-2 whitespace-nowrap">
                                                                <div class="text-lg text-right">
                                                                    {{ $mtoigv }}
                                                                </div>
                                                            </td>
                                                            <td></td>
                                                        </tr>

                                                    </tbody>

                                                    <tfoot>

                                                        <tr>

                                                            {{-- <td>{{ $valorventa }}</td>
                                                            <td>{{ $totalimpuestos }}</td>
                                                            <td>{{ $subtotall }}</td> --}}
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>{{ $totalenletras }}</td>
                                                            <td class="text-right">Total: {{ $moneda }}</td>
                                                            <td class="p-2 whitespace-nowrap">
                                                                <div class="text-lg text-right">
                                                                    {{ $subtotal }}
                                                                </div>
                                                            </td>

                                                            <td></td>
                                                        </tr>
                                                    </tfoot>

                                                </table>
                                               {{--  <div>
                                                    mtoigv = {{ $mtoigv }} /
                                                    mtoigvgratuitas = {{ $mtoigvgratuitas }}  /
                                                    icbper= {{ $icbper }}
                                                    totalimpuestos={{ $totalimpuestos }}
                                                    valorventa={{ $valorventa }}
                                                </div> --}}

                                            </div>
                                        </div>
                                    </div>

                                @else
                                    <h5 class="text-center text-muted">Agrega productos para la venta</h5>
                                @endif


                            </div>
                        </section>


                        <x-jet-danger-button wire:click="save" wire:loading.attr="disabled" wire:target="save"
                            class="w-full mt-4 mb-3 disabled:opacity-25">
                            <i class="mx-2 fa-regular fa-floppy-disk"></i> Guardar
                        </x-jet-danger-button>





                    </div>
                </div>

            </div>



        </div>

    </div>

    {{-- </form> --}}






    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
    @endpush

    @push('scripts')
        <script src="sweetalert2.all.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.slim.js"></script>
        <script src="https://cdn.ckeditor.com/4.14.0/standard/ckeditor.js"></script>
        {{--  <script src="/adminlte/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script> --}}

        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
        <script src="pikaday.js"></script>

        <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

        {{-- <script>
            var datepicker = new Pikaday({
                field: document.getElementById('datepicker'),
                format: 'D MMM YYYY',
                onSelect: function(selectedDate) {
                    Livewire.emit('fechaemision', selectedDate);
                }
            });
        </script> --}}

        {{-- <script>
            var datepicker = new Pikaday({
                field: document.getElementById('datepicker'),
                format: 'D MMM YYYY',
                onSelect: function(selectedDate) {
                    @this.set('fechaemision', selectedDate);
                }
            });
        </script> --}}


        {{-- <script>
            var datepicker = new Pikaday({
                field: document.getElementById('datepicker'),
                format: 'D MMM YYYY',
                onSelect: function(selectedDate) {
                    var formattedDate = moment(selectedDate).format('DDMMYYYY');
                    Livewire.emit('fechaemision', formattedDate);
                }
            });
        </script> --}}


        <script>
            var datepicker = new Pikaday({
                field: document.getElementById('datepicker'),
                format: 'DD MM YYYY', // Cambiado a 'DD/MM/YYYY'//format: 'D MMM YYYY',
                onSelect: function(selectedDate) {
                    var formattedDate = moment(selectedDate).format('DD MM YYYY');
                    @this.set('fechaemision', formattedDate);
                }
            });

            var datepicker2 = new Pikaday({
                field: document.getElementById('datepicker2'),
                format: 'DD MM YYYY',
                onSelect: function(selectedDate) {
                    var formattedDate = moment(selectedDate).format('DD MM YYYY');
                    @this.set('fechavencimiento', formattedDate);
                }
            });


            /* var datepicker2 = new Pikaday({
                field: document.getElementById('datepicker2'),
                format: 'D MMM YYYY',
                onSelect: function(selectedDate) {
                    var formattedDate = moment(selectedDate).format('DD/MM/YYYY');
                    @this.set('fechavencimiento', formattedDate);
                }
            }); */
        </script>


        <script>
            CKEDITOR.replace('editor');
            CKEDITOR.config.height = 115;
        </script>


        {{-- para eliminar un item de la venta --}}
        <script>
            Livewire.on('deleteTemporal', temporalId => {
                Swal.fire({
                    title: 'Estas seguro?',
                    text: "No se podrá revertir!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si, Eliminar!'
                }).then((result) => {
                    if (result.isConfirmed) {

                        Livewire.emitTo('admin.comprobante-create', 'delete', temporalId);

                        Swal.fire(
                            'Eliminado!',
                            'El Registro fue eliminado.',
                            'success'
                        )
                    }
                })
            })
        </script>

        {{-- para eliminar todo de la venta --}}
        <script>
            Livewire.on('limpiarTemporal', () => {
                Swal.fire({
                    title: 'Estas seguro?',
                    text: "No se podrá revertir!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si, Eliminar!'
                }).then((result) => {
                    if (result.isConfirmed) {

                        Livewire.emitTo('admin.comprobante-create', 'limpiar');

                        Swal.fire(
                            'Eliminado!',
                            'El Registro fue eliminado.',
                            'success'
                        )
                    }
                })
            })
        </script>
    @endpush
</div>
