<!DOCTYPE html>
<html lang="ar" dir="rtl" >

<head>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@500&display=swap');
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Invoice</title>
    <style>
        /* Add CSS styles here */
            @page {
        margin: 0px ;
        size: 10.16cm 15.24cm;
        }

        @page :blank {
            display: none;
        }


        .invoice-table {
            /* width: 100%; */
        }

        table, td, th {
            font-size: 14px;
        }

        .invoice-table,
        .invoice-table th,
        .invoice-table td {
            padding: 0px;
        }

        /* Hide borders for inner tables */
        .invoice-table table,
        .invoice-table table th,
        .invoice-table table td {
            border: none;
        }

        .invoice-table {

             border: 0px;
            page-break-inside: avoid;
        }

        .va {
            vertical-align: top;
        }

        .ca {
            text-align: center;
        }

        .va strong {
           /* // font-family: system-ui; */
        }
        body {
            font-family: "Tajawal", sans-serif;
            font-weight: 500;
            font-style: normal;
        }


    </style>
</head>
<body>


    <div class="invoice-print">
        @foreach ($Orders as $order)
            @for ($i = 1; $i <= $order->number_of_pieces; $i++)
<center>
            <table border="1" class="invoice-table" style="direction: ltr; border: 1px solid #030303; width: 100%; height: 99%; page-break-inside: avoid;">
                <tr style="text-align: center;">
                            <td style="width: 33%">
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('uploads/' . $OrganizationProfile->logo))) }}" alt="Company Logo" style="max-width: 80px;">

                            </td>
                            <td>
                                <div style="margin: 5px;">

                                    <img src="data:image/png;base64, {!! base64_encode(QrCode::size(80)->generate('https://app.proofast.com/order-tracking/' . $order->tracking_number)) !!} ">


                                </div>
                            </td>
                            <td style="width: 33%">
                                <table  width="100%" height="100%"  class="ca">
                                    <tr style="border: 1px solid #030303;!important">
                                        <td class="vac">
                                            {{ $order->receiverArea->country_code }} ,
                                            {{ $order->receiverSubArea->area_code }}
                                        </td>
                                    </tr>

                                    <tr style="border: 1px solid #030303 !important;">

                                        <td class="vac">
                                            <hr/>
                                            {{ $order->service->ServiceCode }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="border: 0px;">
                                <table border="1" style="width: 100%; text-align: start; vertical-align: top;">
                                    <tr>
                                        <td class="va" style="width: 50%; border: 1px solid #030303; padding: 10px;">
                                            <strong>From:</strong><br>
                                            {{ $order->sender_name }}<br>
                                            <strong>Mobile:</strong> {{ $order->sender_phone }}<br>
                                            <strong>City:</strong> {{ $order->senderSubArea->name }}<br>
                                            <strong>Address:</strong> {{ $order->sender_address }}<br>
                                            {{ $order->senderArea->country_code }}
                                        </td>
                                        <td class="va" style="width: 50%; border: 1px solid #030303; padding: 10px;">
                                            <strong>To:</strong><br>
                                            {{ $order->receiver_name }}<br>
                                            <strong>Mobile:</strong> {{ $order->receiver_phone_no }}<br>
                                            <strong>City:</strong> {{ $order->receiverSubArea->name }}<br>
                                            <strong>Address:</strong> {{ $order->receiver_address }}<br>
                                            {{ $order->receiverArea->country_code }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr style="text-align: center;">
                            <td colspan="3" style="text-align: center; padding: 5px">
                                <img class="barcode" src="data:image/png;base64, {!! DNS1D::getBarcodePNG($order->tracking_number, 'C128', 2, 50) !!}"
                                     alt="Barcode">
                            </td>
                        </tr>

                        <tr style="text-align: center;">
                            <td colspan="3" style="text-align: center; padding: 5px">
                                <strong>Tracking Number:</strong> #{{ $order->tracking_number }}<br>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="3" style="border: 0px;">
                                <table style="width: 100%; text-align: start; vertical-align: top;">
                                    <tr>
                                        <td style="width: 50%; border: 1px solid #030303; padding: 10px;">
                                            <strong>Ref # </strong> {{ $order->orderRef }}<br>
                                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y H:i:s') }}<br>
                                            <strong>Pieces:</strong> {{ $order->number_of_pieces }}<br>
                                            <strong>Weight:</strong> {{ $order->order_weight }} Kg<br>
                                            <strong>Order Value:</strong> {{ $order->order_fees }}<br>
                                        </td>
                                        <td style="width: 50%; border: 1px solid #030303; padding: 10px; text-align: center;">
                                            <strong>COD:</strong> {{ $order->order_value ? $order->order_value : 0 }}<br>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                        </tr>

                        <tr style="text-align: center;">
                            <td colspan="3" style="text-align: center; padding: 5px">
                                <strong>Description:</strong> {{ $order->note ? $order->note : '----' }}<br>
                            </td>
                        </tr>
                    </table>
                </center>

                    @endfor
        @endforeach
    </div>

</body>
</html>
