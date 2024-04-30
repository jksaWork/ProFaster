<?php

namespace App\Http\Controllers\Shippments;

use App\Events\UpdateShippemntsEvent;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientServicePrice;
use App\Models\Order;
use App\Models\orderTracking;
use App\Models\SallaMerchant;
use App\Models\SallaOrders;
use App\Models\SerialSetting;
use App\Models\Service;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;


class ShipmentsController extends Controller
{

    public function webhock2(Request $request)
    {
        info('--------------- new Shippment Request Was Comming ----------------------');
        info($request);
        if($request->event == 'order.shipment.creating'){
            try {
                
                $data = $request['data'];
                $shipments = $data['shipments'][0];


                $ship_from = $shipments['ship_from'];
                $ship_to =  $shipments['ship_to'];
                $weight = $data['total_weight'];


                $validatedData = [
                    'service_id' => 1,
                    'sender_name' =>$ship_from['name'] ,
                    'sender_phone' => $ship_from['phone'],
                    'sender_address' => $ship_from['address_line'],
                    'sender_area_id' => 1,
                    'sender_sub_area_id' => 1,

                    'receiver_name' => $ship_to['name'],
                    'receiver_phone_no' => $ship_to['phone'],
                    'receiver_area_id' => 1,
                    'receiver_sub_area_id' => 1,
                    'receiver_address' => $ship_to['address_line'],

                    'payment_method' => $shipments['payment_method']  != 'cod'?  "on_receiving":"balance",
                    'cod_method' => $shipments['payment_method']  == 'cod'?  "cash":"netweork", 
                    'order_fees' => $shipments['total']['amount'],
                    'note' => 'nullable',
                    'number_of_pieces' => count($shipments['packages']),
                    'order_weight' => $weight,
                    "status" => 'pending', 
                    'image' => "hello world", 
                ];
                

               $sallaMerchant = SallaMerchant::with('Client')->where('merchant_id', $request->merchant)->first();  
               
               $Client = $sallaMerchant->Client;
                $status = $Client->update([
                    "in_accounts_order" => 1,
                ]);
                
                $police_file_path = '';
                $receipt_file_path = '';

                if ($Client->is_has_custom_price) {
                    $validatedData['delivery_fees'] = (int) filter_var(ClientServicePrice::where('service_id', $request->service_id)->where('client_id', $Client->id)->first()->price, FILTER_SANITIZE_NUMBER_INT);
                    $validatedData['total_fees'] =  $validatedData['order_fees'];
                } else {
                    $validatedData['delivery_fees'] = (int) filter_var(Service::find($request->service_id ?? 1)->price, FILTER_SANITIZE_NUMBER_INT);
                    $validatedData['total_fees'] =  $validatedData['order_fees'];
                }
                $validatedData['representative_deserves'] = $validatedData['delivery_fees'] * env('REPRESENTATIVE_PERCENTAGE') / 100;
                $validatedData['company_deserves'] = $validatedData['delivery_fees'] - $validatedData['representative_deserves'];
                // $validatedData['is_payment_on_delivery'] = $validatedData['is_payment_on_delivery'] ? 1 : 0;
                $validatedData['order_date'] = date('Y-m-d H:i:s');
                $validatedData['status'] = 'pending';
                //base46Image
                $validatedData['police_file'] = $police_file_path;
                //base46Image
                $validatedData['receipt_file'] = $receipt_file_path;
                $validatedData['client_id'] = $Client->id;
                $validatedData['tracking_number'] = orderTracking::generateUniqueTrackingNumber();
                //generate invoice no
                $inv_no = SerialSetting::first()->inv_no;
                SerialSetting::first()->update(["inv_no" => ($inv_no + 1)]);
                $validatedData['invoice_sn'] = genInvNo($inv_no);
                // dd($validatedData);                -----------------------------
                $order = Order::create($validatedData);
                // descount account blance from a user ----------------------------
                $Client->account_balance = $Client->account_balance +  $validatedData['total_fees'];
                $Client->save();

                orderTracking::insertOrderTracking($order->id, __('translation.' . $order->status), " تم اضافه طلب جديد بواسطه  " . $Client->fullname . " بتاريخ  " . $order->created_at, $Client->fullname, $Client->id, " تمت اضافه عنصر بواسطه  " . $Client->fullname . 'في' . $order->created_at);
                $sallOrder = SallaOrders::create([
                    'order_id' => $order->id , 
                    'salla_order_id' => $data['id'],
                    'shipping_number' => "". $data['reference_id'] . "" , 
                    'tracking_number' => $shipments['tracking_number'], 
                    'shipment_id' => $shipments['id'], 
                    'merchant' => $request->merchant,
                    'salla_order_status' => $data['status']['slug'],
                    'salla_shipment_status' => $shipments['status'] 
                ]);

                event( new UpdateShippemntsEvent($order , $sallOrder, $sallaMerchant));
                if ($order) {
                    return "Order Saved Sccueefuly";
                }
            } catch (Exception $e) {
                throw $e;
                return response([
                    'status' => false,
                    'message' => 'Something Went Wrong'
                ]);
            }
        }
    }
}
