@extends('layouts.master')
@push('links')
    <style id='inline_style'>
        h4 {
            font-weight: bold;
        }

        h2 {
            margin-top: 50px;
        }

        .breaker{
            page-break-before: always;
        }
    </style>
@endpush
@section('content')
    @php
        $totalBlade = 0;
        $DeliveryFess = [];
        $totalOfService = 0;
        $newOrder = $Orders->groupBy('service_id');
        $total_quntity = 0;
        $total_of_fees = 0;
    @endphp
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 col-12 mb-2">
                <h3 class="content-header-title mb-0">{{ __('translation.clients.management') }}</h3>
                <div class="row breadcrumbs-top">
                    <div class="breadcrumb-wrapper col-12">
                        {{ Breadcrumbs::render('clients') }}
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-right col-md-6 col-12">
            </div>
        </div>
        <div class="content-body">
            <!-- Zero configuration table -->
            <section id="configuration">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                                <div class="heading-elements">
                                    <ul class="list-inline mb-0">
                                        <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                        <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                        <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                                        {{-- <li><a data-action="close"><i class="ft-x"></i></a></li> --}}
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body ">
                                <ul class="HiddenInPrint nav nav-tabs nav-linetriangle no-hover-bg "
                                    style="border-bottom-color:#1e9ff2">
                                    <li class="nav-item ">
                                        <a class="nav-link active" id="base-tab41" data-toggle="tab" aria-controls="tab41"
                                            href="#tab41" aria-expanded="true">{{ __('translation.data.issue') }}</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link " id="base-tab42" data-toggle="tab" aria-controls="tab42"
                                            href="#tab42" aria-expanded="false">{{ __('translation.file.issue') }}</a>
                                    </li>
                                </ul>
                                <div class="tab-content px-1 pt-1 ">
                                    <div role="tabpanel" class="tab-pane active" id="tab41" aria-expanded="true"
                                        aria-labelledby="base-tab41">
                                        <p>
                                        <div class="card-content collapse show">
                                            <div class="card-body card-dashboard" id='card-dashboard' >
                                                <div class="d-flex justify-content-between align-items-center my-1">
                                                    <div class="col-md-4"></div>
                                                    <div class="col-md-4 text-center inprintOnly">
                                                        <h1>
                                                            invocie - {{__('translation.invoice')}}
                                                        </h1>
                                                    </div>
                                                    <div class="col-md-4  p-2 text-right inprintOnly">
                                                        <div class="imgContinaer text-right inprintOnly">
                                                            <img src="{{asset('uploads/' . $OrganizationProfile->logo)}}"
                                                                alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- prefrix the print section --}}
                                                <div class="d-flex justify-content-between align-items-center my-1">
                                                    <div class="col-md-6 inprintOnly p-2">
                                                        <div class="imgContinaer">
                                                            <h3> <b>{{__('translation.name.of.company')}}</b> : {{
                                                               '  ' . $OrganizationProfile->name}} </h3>
                                                                <h3>
                                                                    <span><b>{{__('translation.name')}} :</b></span>
                                                                    <span>
                                                                        {{$ClientStatementIsues->Client->fullname }}
                                                                    </span>
                                                                    {{-- @dd($ClientStatementIsues->status); --}}
                                                                </h3>

                                                                 <h3>
                                                                    <span><b>رمز العميل :</b></span>
                                                                    <span>
                                                                      GIZ-  {{$ClientStatementIsues->Client->id }}
                                                                    </span>
                                                                    {{-- @dd($ClientStatementIsues->status); --}}
                                                                </h3>

                                                                <h3>
                                                                    <span><b>رقم الفاتورة :</b></span>
                                                                    <span>
                                                                        {{$ClientStatementIsues->id }}
                                                                    </span>
                                                                    {{-- @dd($ClientStatementIsues->status); --}}
                                                                </h3>
                                                                <h3>
                                                                    <span><b>{{__('translation.issue_date')}} :</b></span>
                                                                    <span>
                                                                        {{ "  ".date('y-m-d') ." | ". date('Y-M-D') }}
                                                                    </span>
                                                                </h3>
                                                            </div>
                                                    </div>
                                                    <div class="col-md-6  p-2 text-right inprintOnly">
                                                        <div class="imgContinaer text-right inprintOnly">
                                                            {{-- <img src="{{asset('uploads/' . $OrganizationProfile->logo)}}"
                                                                alt=""> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- end prefix section --}}
                                                <div class="HiddenInPrint">
                                                    <div class=" d-flex justify-content-between ">
                                                        <h3>
                                                            <span>
                                                                {{ $ClientStatementIsues->Client->fullname }}
                                                            </span>
                                                            {{-- @dd($ClientStatementIsues->status); --}}
                                                            <span
                                                                class="badge badge-sm mx-1 @switch($ClientStatementIsues->status)
                                                            @case('paid')
                                                                badge-success
                                                                @break
                                                            @case('unpaid')
                                                                badge-warning
                                                                @break
                                                            @default
                                                        @endswitch ">{{ __('translation.' . $ClientStatementIsues->status) }}</span>
                                                        </h3>
                                                        <div>
                                                            <script>
                                                                function Print(){
                                                                    var divContents = document.getElementById("card-dashboard").innerHTML;
                                                                    var styles = document.getElementById("inline_style").innerHTML;
                                                                    var header = document.querySelector('head').innerHTML;
                                                                    var a = window.open('', '', 'height=1000, width=1000');
                                                                    a.document.write('<html>');
                                                                    a.document.write(header);
                                                                    a.document.write('<style>');
                                                                    a.document.write('html, body , html body {background:#fff; padding:10px}');
                                                                    a.document.write(styles);
                                                                    a.document.write('</style>');
                                                                    a.document.write(divContents);
                                                                    a.document.write('</body></html>');
                                                                    setTimeout(() => {
                                                                        a.print();
                                                                    }, 3000);
                                                                    a.document.close();
                                                                    // console.log(a.document.readyState === "complete" || a.document.readyState === "interactive");
                                                                }
                                                            </script>
                                                            <button
                                                                onclick="Print()"
                                                                href="#tab42" class="btn btn-sm btn-primary">
                                                                {{-- <i class="la  la-sm la-print"></i> --}}
                                                                {{ __('translation.print') }}
                                                            </button>

                                                            <button id="base-tab42" data-toggle="tab" aria-controls="tab42"
                                                                onclick="document.getElementById('base-tab42').click()"
                                                                href="#tab42" class="btn btn-sm btn-primary">
                                                                {{ __('translation.add.file') }}
                                                            </button>
                                                            @if ($ClientStatementIsues->status != 'paid')
                                                                <a href="{{ route('issue.status', $ClientStatementIsues->id) }}"
                                                                    class="btn btn-sm btn-success">
                                                                    {{ __('translation.asCompleted') }}
                                                                </a>
                                                            @else
                                                                <a href="{{ route('issue.status', $ClientStatementIsues->id) }}"
                                                                    class="btn btn-sm btn-warning">
                                                                    {{ __('translation.asNotCommpleted') }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="inprintOnly">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <h3>
                                                                <span>
                                                                    <b>{{ __('translation.client.name') }} : </b>
                                                                </span>
                                                                <span>
                                                                    {{ $ClientStatementIsues->Client->fullname }}
                                                                </span>
                                                            </h3>
                                                        </div>
                                                        <div>
                                                            <h3>
                                                                <span>
                                                                    <b>{{ __('translation.status') }} : </b>
                                                                </span>
                                                                <span>
                                                                    {{ __('translation.' . $ClientStatementIsues->status) }}
                                                                </span>
                                                            </h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="order_info ">
                                                    @foreach ($newOrder as $keyy => $newOrderCollection)
                                                        @if ($keyy == 1)
                                                            @php
                                                                $ServiceOne = $newOrderCollection->filter(fn($item) => $item->order_value != 0);
                                                                $Cod = $newOrderCollection->filter(fn($item) => $item->order_value == 0);
                                                            @endphp
                                                            @if (count($ServiceOne) > 0)
                                                                <h2> {!! $ServiceHeading[$Services->find($keyy)->id][0] !!}</h2>
                                                                <table class="table table-striped table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th style="width: 3px">
                                                                                {{ __('translation.No') }}</th>
                                                                            <th>{{ __('translation.order.date') }}</th>
                                                                            {{-- <th>{{ __('translation.service') }}</th> --}}
                                                                            <th>{{ __('translation.receiver.name') }}</th>
                                                                            <th>{{ __('translation.order_value') }}</th>
                                                                            {{-- <th>{{ __('translation.order_value_on_delverd') }}</th> --}}
                                                                            <th class='HiddenInPrint'>{{ __('translation.action') }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        {{-- @if (count($ServiceOne) > 0) --}}
                                                                        @foreach ($ServiceOne as $key => $order)
                                                                            <tr>
                                                                                <td>{{ $order->tracking_number }}</td>
                                                                                <td>{{ $order->order_date }}</td>
                                                                                {{-- <td>{{ $order->service->name }}</td> --}}
                                                                                <td>{{ $order->receiver_name }}</td>
                                                                                </td>
                                                                                <td>{{ $order->order_value }}</td>
                                                                                {{-- <td>{{ $order->order_fees }}</td> --}}
                                                                                <td class='HiddenInPrint'>
                                                                                    <a href="{{ route('print.invoice', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon btn-warning"><i
                                                                                            class="la la-print"></i></a>
                                                                                    <a {{-- data-toggle="modal" data-target="#showModal{{$order->id}}" --}}
                                                                                        href="{{ route('orders.show.details', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon
                                                                            btn-info"><i
                                                                                            class="la la-info"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            @endif
                                                            @if (count($Cod) > 0)
                                                                {{-- @dd($Services->find($keyy) , $key); --}}
                                                                <h2> {!! $ServiceHeading[$Services->find($keyy)->id][1] !!}</h2>
                                                                <table class="table table-striped table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th style="width: 3px">
                                                                                {{ __('translation.No') }}</th>
                                                                            <th>{{ __('translation.order.date') }}</th>
                                                                            {{-- <th>{{ __('translation.service') }}</th> --}}
                                                                            <th>{{ __('translation.receiver.name') }}</th>
                                                                            {{-- <th>{{ __('translation.order_value_on_delverd') }}</th> --}}
                                                                            <th class='HiddenInPrint'>{{ __('translation.action') }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        {{-- @if (count($ServiceOne) > 0) --}}
                                                                        @foreach ($Cod as $key => $order)
                                                                            <tr>
                                                                                <td>{{ $order->tracking_number }}</td>
                                                                                <td>{{ $order->order_date }}</td>
                                                                                {{-- <td>{{ $order->service->name }}</td> --}}
                                                                                <td>{{ $order->receiver_name }}</td>
                                                                                {{-- <td>{{ $order->order_fees }}</td> --}}
                                                                                <td class='HiddenInPrint'>
                                                                                    <a href="{{ route('print.invoice', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon btn-warning"><i
                                                                                            class="la la-print"></i></a>
                                                                                    <a {{-- data-toggle="modal" data-target="#showModal{{$order->id}}" --}}
                                                                                        href="{{ route('orders.show.details', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon
                                                                            btn-info"><i
                                                                                            class="la la-info"></i></a>
                                                                                </td>
                                                                            </tr>

                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            @endif
                                                            @continue
                                                        @endif
                                                        {!! $ServiceHeading[$Services->find($keyy)->id] !!}
                                                        <table class="table table-striped table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 3px">{{ __('translation.No') }}
                                                                    </th>
                                                                    <th>{{ __('translation.order.date') }}</th>
                                                                    {{-- <th>{{ __('translation.service') }}</th> --}}
                                                                    <th>{{ __('translation.receiver.name') }}</th>
                                                                    {{-- <th>{{ __('translation.total.fees') }}</th> --}}
                                                                    {{-- <th>{{ __('translation.order_value_on_delverd') }}</th> --}}
                                                                    <th  class='HiddenInPrint'>{{ __('translation.action') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if (count($newOrderCollection) > 0)
                                                                    @foreach ($newOrderCollection as $key => $order)
                                                                        <tr>
                                                                            <td>{{ $order->tracking_number }}</td>
                                                                            <td>{{ $order->order_date }}</td>
                                                                            {{-- <td>{{ $order->service->name }}</td> --}}
                                                                            <td>{{ $order->receiver_name }}</td>
                                                                            {{-- <td>{{ $order->order_fees }}</td> --}}
                                                                            <td class='HiddenInPrint'>
                                                                                <a href="{{ route('print.invoice', $order->id) }}"
                                                                                    class="btn btn-sm btn-icon btn-warning"><i
                                                                                        class="la la-print"></i></a>
                                                                                <a {{-- data-toggle="modal" data-target="#showModal{{$order->id}}" --}}
                                                                                    href="{{ route('orders.show.details', $order->id) }}"
                                                                                    class="btn btn-sm btn-icon btn-info"><i
                                                                                        class="la la-info"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @else
                                                                    <tr class="text-center">
                                                                        <td colspan="10">
                                                                            {{ __('translation.table.empty') }}</td>
                                                                    </tr>
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    @endforeach
                                                </div>
                                                <div class="mt-5 breaker" >
                                                    <h4>{{__('translation.order_invoice')}}</h4>
                                                    <div class="table-responsive">
                                                        <table class="table datatable table-striped table-bordered">
                                                            <thead style="background:#d7e3bd">
                                                                <tr>
                                                                    <th class="text-center">
                                                                        <div>
                                                                            <h4 class="">Total</h4>
                                                                        </div>
                                                                        <div>
                                                                            <h4 class="">
                                                                                {{ __('translation.service') }}</h4>
                                                                        </div>
                                                                    </th>
                                                                    <th class="text-center">
                                                                        <div>
                                                                            <h4 class="">Qunitity</h4>
                                                                        </div>
                                                                        <div>
                                                                            <h4 class="">
                                                                                {{ __('translation.qunitity') }}</h4>
                                                                        </div>
                                                                    </th>
                                                                    <th class="text-center">
                                                                        <div>
                                                                            <h4 class="">Service Charges</h4>
                                                                        </div>
                                                                        <div>
                                                                            <h4 class="">
                                                                                {{ __('translation.service_fees') }}</h4>
                                                                        </div>
                                                                    </th>
                                                                    <th class="text-center">
                                                                        <div>
                                                                            <h4 class="">total</h4>
                                                                        </div>
                                                                        <div>
                                                                            <h4 class="">
                                                                                {{ __('translation.total') }}</h4>
                                                                        </div>
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                            @if (count($newOrder) > 0)
                                                        @foreach ($newOrder as $key => $newOrderCollection)
                                                            {{-- @if( $newOrderCo) --}}
                                                            @if ($key == 1)
                                                            @php
                                                                $ServiceOne  = $newOrderCollection->filter(fn($item) => $item->order_value !=0);
                                                                $Cod  = $newOrderCollection->filter(fn($item) => $item->order_value ==0);
                                                            @endphp
                                                            @if (count($ServiceOne) > 0)
                                                            <tr>
                                                                <td>{!! $ServiceHeading[$Services->find($key)->id][0] !!}</td>
                                                                <td>
                                                                    @php
                                                                        $total_quntity += count($ServiceOne);
                                                                        echo count($ServiceOne);
                                                                    @endphp
                                                                </td>
                                                                <td>
                                                                    @if ($client->is_has_custom_price)
                                                                        @php
                                                                            $price = $client->ServicePrice->where('service_id', $key)->first()->price;
                                                                            echo $price;
                                                                        @endphp
                                                                    @endif
                                                                    @if (!$client->is_has_custom_price)
                                                                        @php
                                                                            $price = $Services->where('id', $key)->first()->price;
                                                                            echo $price;
                                                                        @endphp
                                                                    @endif
                                                                </td>
                                                                <td> @php
                                                                    $total_of_fees += count($ServiceOne) * $price;
                                                                    echo count($ServiceOne) * $price ;
                                                                    @endphp</td>
                                                            </tr>
                                                            @endif
                                                            @if (count($Cod) > 0)
                                                            <tr>
                                                                <td>{!!  $ServiceHeading[$Services->find($key)->id][1] !!}</td>
                                                                <td>
                                                                    @php
                                                                        $total_quntity += count($Cod);
                                                                        echo count($Cod);
                                                                    @endphp
                                                                </td>
                                                                <td>
                                                                    @if ($client->is_has_custom_price)
                                                                        @php
                                                                            $price = $client->ServicePrice->where('service_id',$key) -> where('type','pickup')->first()->price;
                                                                            echo $price;
                                                                        @endphp
                                                                    @endif
                                                                    @if (!$client->is_has_custom_price)
                                                                        @php
                                                                            $price = $Services->where('id', $key)->first()->cod;
                                                                            echo $price;
                                                                        @endphp
                                                                    @endif
                                                                </td>
                                                                <td> @php
                                                                    $total_of_fees += count($Cod) * $price;
                                                                    echo count($Cod) * $price ;
                                                                    @endphp</td>
                                                            </tr>
                                                            @endif
                                                            @continue
                                                            @endif





                                                            <tr>
                                                                <td>{!! $ServiceHeading[$Services->find($key)->id] !!}</td>
                                                                <td>
                                                                    @php
                                                                        $total_quntity += count($newOrderCollection);
                                                                        echo count($newOrderCollection);
                                                                    @endphp
                                                                </td>
                                                                <td>
                                                                    @if ($client->is_has_custom_price)
                                                                        @php
                                                                            $price = $client->ServicePrice->where('service_id', $key)->first()->price;
                                                                            echo $price;
                                                                        @endphp
                                                                    @endif
                                                                    @if (!$client->is_has_custom_price)
                                                                        @php
                                                                            $price = $Services->where('id', $key)->first()->price;
                                                                            echo $price;
                                                                        @endphp
                                                                    @endif
                                                                </td>
                                                                <td> @php
                                                                    $total_of_fees += count($newOrderCollection) * $price;
                                                                    echo count($newOrderCollection) * $price ;
                                                                    @endphp</td>
                                                            </tr>





                                                        @endforeach
                                                        <tr>
                                                            <td>
                                                                <h4>total </h4>
                                                                <h4>{{ __('translation.total')}} </h4>
                                                            </td>
                                                            <td>{{ $total_quntity }}</td>
                                                            <td> </td>
                                                            <td> {{ $total_of_fees}}</td>
                                                        </tr>



                                                    @else
                                                        <tr class="text-center">
                                                            <td colspan="10">{{ __('translation.table.empty') }}</td>
                                                        </tr>
                                                    @endif
                                                    @if (count($newOrder) > 0)
                                                    <tr>
                                                        {{-- <td></td> --}}
                                                        <td colspan="2" class="text-rigth">
                                                            <h4 class="text-right">
                                                                Totol COD amount
                                                            </h4>
                                                        </td>
                                                        <td><h4 >
                                                            {{ __('translation.cod_total_sum')}}</h4></td>
                                                        <td style="background:#d7e3bd"><h4>
                                                            {{-- @dd($Cod); --}}
                                                            {{ isset($ServiceOne) ? $ServiceOne->sum('order_value') : 0}}
                                                        </h4></td>
                                                    </tr>
                                                      <tr>
                                                        <td colspan="2">
                                                            <h4 class='text-right'>
                                                                Total Service Charges
                                                            </h4>
                                                        </td>
                                                        <td>
                                                            <h4>
                                                                {{ __('translation.total_fess_deserve')}}
                                                            </h4>
                                                        </td>
                                                        <td style='background:#d7e3bd'><h4>{{ $total_of_fees }}</h4></td>
                                                    </tr>
                                                    <tr class="d-none">
                                                        {{-- <td></td> --}}
                                                        <td colspan="2" class="text-rigth">
                                                            <h4 class="text-right">
                                                                TAX
                                                            </h4>
                                                        </td>
                                                        <td><h4 >
                                                            {{ __('translation.tax')}}</h4></td>
                                                        <td style="background:#d7e3bd"><h4>
                                                            {{-- @dd($Cod); --}}
                                                            @php
                                                            $tax_value=
                                                            $client_tax_value = 0;
                                                                if(isset($ServiceOne)){
                                                                $client_tax_value =  $client_tax_value = $total_of_fees  * 0;//0.15;
                                                                }
                                                                echo $client_tax_value;
                                                            @endphp
                                                        </h4></td>
                                                    </tr>



                                                    <tr>
                                                        {{-- <td colspan="2"></td> --}}
                                                        {{-- <td></td> --}}
                                                        {{-- @dd($Cod->sum('total_fees')); --}}
                                                        <td colspan="2"><h4 class='text-right'> net. Total </h4></td>
                                                        <td>
                                                            <h4>
                                                            {{ __('translation.free_total')}}
                                                            </h4>
                                                        </td>
                                                        <td style="background:#d7e3bd">
                                                        <h4>
                                                            {{  (( isset($ServiceOne) ? $ServiceOne->sum('order_value'): 0) - $client_tax_value) - $total_of_fees }}
                                                        </h4>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                                        </table>
                                                    </div>


                                                   <div class="mt-5 breaker" >
                                                    <h4>توضيح</h4>
                                                    <div class="table-responsive">
                                                        <table class="table datatable table-striped table-bordered">
                                                            <thead style="background:#d7e3bd">
                                                                <tr>
                                                                    <th class="text-center">
                                                                        <div>
                                                                            <h4 class="">Total</h4>
                                                                        </div>
                                                                        <div>
                                                                            <h4 class="">
                                                                                {{ __('translation.service') }}</h4>
                                                                        </div>
                                                                    </th>
                                                                    <th class="text-center">
                                                                        <div>
                                                                            <h4 class="">Qunitity</h4>
                                                                        </div>
                                                                        <div>
                                                                            <h4 class="">
                                                                                {{ __('translation.qunitity') }}</h4>
                                                                        </div>
                                                                    </th>


                                                            </thead>


                                                                </tr>
                                                                <tr>
                                                                <td>
                                                                <h4> {{ __('translation.number_order_count_out_the_clac')}}  (قيد التنفيذ)<h4>
                                                          </td>
                                                                <td>
                                                                {{-- @dd($Cod); --}}
                                                           {{ $client->orders->whereIn('status' , [ 'pickup' , 'inProgress'])->where('is_collected', '0')->count() }}
                                                        </td>

                                                            </tr>




                                                            <tbody>
                                                    </table>

                                                   <h3>التفاصيل :</h3>
                                                      <br>
                                                     @php
                                                            $new = $client->orders->whereIn('status' , [ 'pickup' , 'inProgress'])->where('is_collected', '0')->whereIn('service_id', '1');

                                                            @endphp

                                                               @if (count($new) > 0)
                                                                                                                   <h4>توصيل الطلبات للمتاجر</h2>
                                                    <table>
                                                         <table class="table table-striped table-bordered">

                                                               <thead>
                                                                <tr>
                                                                    <th style="width: 3px">{{ __('translation.No') }}
                                                                    </th>
                                                                    <th>{{ __('translation.order.date') }}</th>
                                                                    {{-- <th>{{ __('translation.service') }}</th> --}}
                                                                    <th>{{ __('translation.client') }}</th>
                                                                     <th>{{ __('translation.receiver.name') }}</th>
                                                                        {{-- <th>{{ __('translation.total.fees') }}</th> --}}
                                                                    {{-- <th>{{ __('translation.order_value_on_delverd') }}</th> --}}
                                                                    <th  class='HiddenInPrint'>{{ __('translation.action') }}</th>
                                                                </tr>
                                                            </thead>
                                                     <tbody>
                                                                    @foreach ($new as $order)


                                                                    <tr>
                                                                                <td>{{ $order->tracking_number }}</td>
                                                                                <td>{{ $order->order_date }}</td>

                                                                                <td>{{ $order->client->fullname?? '--' }}</td>
                                                                                <td>{{ $order->receiver_name?? '--' }}</td>
                                                                                {{-- <td>{{ $order->order_fees }}</td> --}}
                                                                                <td class='HiddenInPrint'>
                                                                                    <a href="{{ route('print.invoice', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon btn-warning"><i
                                                                                            class="la la-print"></i></a>
                                                                                    <a {{-- data-toggle="modal" data-target="#showModal{{$order->id}}" --}}
                                                                                        href="{{ route('orders.show.details', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon
                                                                            btn-info"><i
                                                                                            class="la la-info"></i></a>
                                                                                </td>
                                                                            </tr>


                                                                       @endforeach
                                                                       @endif


                                                            <tbody>
                                                    </table>




                                                     @php
                                                            $new = $client->orders->whereIn('status' , [ 'pickup' , 'inProgress'])->where('is_collected', '0')->whereIn('service_id', '2');

                                                            @endphp

                                                               @if (count($new) > 0)
                                                              <h4>

                                                              شحن الطلبات للمتاجر
                                                              </h2>
                                                    <table>
                                                         <table class="table table-striped table-bordered">

                                                                <thead>
                                                                <tr>
                                                                    <th style="width: 3px">{{ __('translation.No') }}
                                                                    </th>
                                                                    <th>{{ __('translation.order.date') }}</th>
                                                                    {{-- <th>{{ __('translation.service') }}</th> --}}
                                                                    <th>{{ __('translation.client') }}</th>
                                                                    <th>{{ __('translation.receiver.name') }}</th>

                                                                    {{-- <th>{{ __('translation.total.fees') }}</th> --}}
                                                                    {{-- <th>{{ __('translation.order_value_on_delverd') }}</th> --}}
                                                                    <th  class='HiddenInPrint'>{{ __('translation.action') }}</th>
                                                                </tr>
                                                            </thead>
                                                     <tbody>
                                                                    @foreach ($new as $order)


                                                                    <tr>
                                                                                <td>{{ $order->tracking_number }}</td>
                                                                                <td>{{ $order->order_date }}</td>

                                                                                <td>{{ $order->client->fullname?? '--' }}</td>
                                                                                 <td>{{ $order->receiver_name?? '--' }}</td>
                                                                                {{-- <td>{{ $order->order_fees }}</td> --}}
                                                                                <td class='HiddenInPrint'>
                                                                                    <a href="{{ route('print.invoice', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon btn-warning"><i
                                                                                            class="la la-print"></i></a>
                                                                                    <a {{-- data-toggle="modal" data-target="#showModal{{$order->id}}" --}}
                                                                                        href="{{ route('orders.show.details', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon
                                                                            btn-info"><i
                                                                                            class="la la-info"></i></a>
                                                                                </td>
                                                                            </tr>


                                                                       @endforeach
                                                                       @endif


                                                            <tbody>
                                                    </table>

                                                     @php
                                                            $new = $client->orders->whereIn('status' , [ 'pickup' , 'inProgress'])->where('is_collected', '0')->whereIn('service_id', '3');

                                                            @endphp

                                                               @if (count($new) > 0)
                                                                                                                     <h4>الشحن الدولي</h2>
                                                    <table>
                                                         <table class="table table-striped table-bordered">

                                                               <thead>
                                                                <tr>
                                                                    <th style="width: 3px">{{ __('translation.No') }}
                                                                    </th>
                                                                    <th>{{ __('translation.order.date') }}</th>
                                                                    {{-- <th>{{ __('translation.service') }}</th> --}}
                                                                    <th>{{ __('translation.client') }}</th>
                                                                    <th>{{ __('translation.receiver.name') }}</th>

                                                                    {{-- <th>{{ __('translation.total.fees') }}</th> --}}
                                                                    {{-- <th>{{ __('translation.order_value_on_delverd') }}</th> --}}
                                                                    <th  class='HiddenInPrint'>{{ __('translation.action') }}</th>
                                                                </tr>
                                                            </thead>
                                                     <tbody>
                                                                    @foreach ($new as $order)


                                                                    <tr>
                                                                                <td>{{ $order->tracking_number }}</td>
                                                                                <td>{{ $order->order_date }}</td>

                                                                                <td>{{ $order->client->fullname?? '--' }}</td>
                                                                                <td>{{ $order->receiver_name?? '--' }}</td>
                                                                                {{-- <td>{{ $order->order_fees }}</td> --}}
                                                                                <td class='HiddenInPrint'>
                                                                                    <a href="{{ route('print.invoice', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon btn-warning"><i
                                                                                            class="la la-print"></i></a>
                                                                                    <a {{-- data-toggle="modal" data-target="#showModal{{$order->id}}" --}}
                                                                                        href="{{ route('orders.show.details', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon
                                                                            btn-info"><i
                                                                                            class="la la-info"></i></a>
                                                                                </td>
                                                                            </tr>


                                                                       @endforeach
                                                                       @endif


                                                            <tbody>
                                                    </table>





                                                     @php
                                                            $new = $client->orders->whereIn('status' , [ 'pickup' , 'inProgress'])->where('is_collected', '0')->whereIn('service_id', '4');

                                                            @endphp

                                                               @if (count($new) > 0)
                                                                                                                     <h4>


                                                                                                                        استرجاع الطلبات من العميل


                                                                                                                         </h2>
                                                    <table>
                                                         <table class="table table-striped table-bordered">

                                                               <thead>
                                                                <tr>
                                                                    <th style="width: 3px">{{ __('translation.No') }}
                                                                    </th>
                                                                    <th>{{ __('translation.order.date') }}</th>
                                                                    {{-- <th>{{ __('translation.service') }}</th> --}}
                                                                    <th>{{ __('translation.client') }}</th>
                                                                    <th>{{ __('translation.receiver.name') }}</th>

                                                                    {{-- <th>{{ __('translation.total.fees') }}</th> --}}
                                                                    {{-- <th>{{ __('translation.order_value_on_delverd') }}</th> --}}
                                                                    <th  class='HiddenInPrint'>{{ __('translation.action') }}</th>
                                                                </tr>
                                                            </thead>
                                                     <tbody>
                                                                    @foreach ($new as $order)


                                                                    <tr>
                                                                                <td>{{ $order->tracking_number }}</td>
                                                                                <td>{{ $order->order_date }}</td>

                                                                                <td>{{ $order->client->fullname?? '--' }}</td>
                                                                                <td>{{ $order->receiver_name?? '--' }}</td>
                                                                                {{-- <td>{{ $order->order_fees }}</td> --}}
                                                                                <td class='HiddenInPrint'>
                                                                                    <a href="{{ route('print.invoice', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon btn-warning"><i
                                                                                            class="la la-print"></i></a>
                                                                                    <a {{-- data-toggle="modal" data-target="#showModal{{$order->id}}" --}}
                                                                                        href="{{ route('orders.show.details', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon
                                                                            btn-info"><i
                                                                                            class="la la-info"></i></a>
                                                                                </td>
                                                                            </tr>


                                                                       @endforeach
                                                                       @endif


                                                            <tbody>
                                                    </table>








                                                     @php
                                                            $new = $client->orders->whereIn('status' , [ 'pickup' , 'inProgress'])->where('is_collected', '0')->whereIn('service_id', '5');

                                                            @endphp

                                                               @if (count($new) > 0)
                                                                                                                     <h4>

                                                                                                                         استرجاع الطلبات بعد محاولة التسليم

                                                                                                                          </h2>
                                                    <table>
                                                         <table class="table table-striped table-bordered">

                                                               <thead>
                                                                <tr>
                                                                    <th style="width: 3px">{{ __('translation.No') }}
                                                                    </th>
                                                                    <th>{{ __('translation.order.date') }}</th>
                                                                    {{-- <th>{{ __('translation.service') }}</th> --}}
                                                                    <th>{{ __('translation.client') }}</th>
                                                                    <th>{{ __('translation.receiver.name') }}</th>

                                                                    {{-- <th>{{ __('translation.total.fees') }}</th> --}}
                                                                    {{-- <th>{{ __('translation.order_value_on_delverd') }}</th> --}}
                                                                    <th  class='HiddenInPrint'>{{ __('translation.action') }}</th>
                                                                </tr>
                                                            </thead>
                                                     <tbody>
                                                                    @foreach ($new as $order)


                                                                    <tr>
                                                                                <td>{{ $order->tracking_number }}</td>
                                                                                <td>{{ $order->order_date }}</td>

                                                                                <td>{{ $order->client->fullname?? '--' }}</td>
                                                                                <td>{{ $order->receiver_name?? '--' }}</td>
                                                                                {{-- <td>{{ $order->order_fees }}</td> --}}
                                                                                <td class='HiddenInPrint'>
                                                                                    <a href="{{ route('print.invoice', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon btn-warning"><i
                                                                                            class="la la-print"></i></a>
                                                                                    <a {{-- data-toggle="modal" data-target="#showModal{{$order->id}}" --}}
                                                                                        href="{{ route('orders.show.details', $order->id) }}"
                                                                                        class="btn btn-sm btn-icon
                                                                            btn-info"><i
                                                                                            class="la la-info"></i></a>
                                                                                </td>
                                                                            </tr>


                                                                       @endforeach
                                                                       @endif


                                                            <tbody>
                                                    </table>



                                                </div>
                                            </div>
                                        </div>
                                        </p>
                                    </div>
                                    <div class="tab-pane " id="tab42" aria-labelledby="base-tab42">
                                        <p>
                                        <form action="{{ route('UploadFiles', $id) }}" method="post"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="">{{ __('translation.add.file') }}</label>
                                                <input type="file" class="form-control" name="file" id=""
                                                    accept="image/*" onchange="this.form.submit()"
                                                    aria-describedby="helpId" placeholder="">
                                            </div>
                                        </form>
                                        <hr>
                                        <div>
                                            @foreach ($ClientStatementIsues->Photos as $item)
                                                <div class="d-flex justify-content-between align-items-center m-1">
                                                    <div class="col-md-3">
                                                        <img src="{{ $item->photo }}" width="70px" height="70px"
                                                            alt="" />
                                                    </div>
                                                    <div>
                                                        <a target="_blank" href="{{ route('showFile', $item->id) }}"
                                                            class="btn btn-sm btn-outline-info">
                                                            <span>
                                                                <svg style="width:15px;height:15px" viewBox="0 0 24 24">
                                                                    <path fill="currentColor"
                                                                        d="M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C12.36,19.5 12.72,19.5 13.08,19.45C13.03,19.13 13,18.82 13,18.5C13,17.94 13.08,17.38 13.24,16.84C12.83,16.94 12.42,17 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12C17,12.29 16.97,12.59 16.92,12.88C17.58,12.63 18.29,12.5 19,12.5C20.17,12.5 21.31,12.84 22.29,13.5C22.56,13 22.8,12.5 23,12C21.27,7.61 17,4.5 12,4.5M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M18,14.5V17.5H15V19.5H18V22.5H20V19.5H23V17.5H20V14.5H18Z" />
                                                                </svg>
                                                            </span>
                                                        </a>
                                                        <a href="{{ route('downloadImage', $item->id) }}"
                                                            class="btn btn-sm btn-outline-success">
                                                            <span>
                                                                <svg style="width:15px;height:15px" viewBox="0 0 24 24">
                                                                    <path fill="currentColor"
                                                                        d="M5,20H19V18H5M19,9H15V3H9V9H5L12,16L19,9Z" />
                                                                </svg>
                                                            </span>
                                                        </a>
                                                        <a href="{{ route('DeletImage2', $item->id) }}"
                                                            class="btn btn-sm
                                                    btn-outline-danger">
                                                            <span>
                                                                <svg style="width:14px;height:14px" viewBox="0 0 24 24">
                                                                    <path fill="currentColor"
                                                                        d="M20.37,8.91L19.37,10.64L7.24,3.64L8.24,1.91L11.28,3.66L12.64,3.29L16.97,5.79L17.34,7.16L20.37,8.91M6,19V7H11.07L18,11V19A2,2 0 0,1 16,21H8A2,2 0 0,1 6,19Z" />
                                                                </svg>
                                                            </span>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
        </div>
        </section>
        <!--/ Zero configuration table -->
    </div>
    </div>
@endsection
