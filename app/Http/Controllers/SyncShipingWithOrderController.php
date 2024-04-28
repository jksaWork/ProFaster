<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderShiping;
use App\Services\Shiping\SMSAShipingService;
use App\Services\Shiping\SMSAShipingServiceAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class SyncShipingWithOrderController extends Controller
{
    public function index()
    {
        $data = OrderShiping::all();
        return view('shipping.index', compact('data'));
    }

    public function syncOrder()
    {
        //dd(request());
        // return ;
        request()->validate([
            'ids' => 'required',
        ]);

        $order_ids = decrypt(request()->ids);
        $orders = Order::find($order_ids);
        // dd($orders);


        //  if(request()->smsaWithSenderEdite)
        return view('shipping.show', compact('orders'));


        // name , phone, address , city , postalcode , wight, number peaces
        // return view('shipping.show');
    }

    public function BulkPrint(Request $request)
    {
        // return ;
        request()->validate([
            'ids' => 'required',
        ]);

        $order_ids = decrypt(request()->ids);

        $orders = Order::with('Shipping')->find($order_ids);
        $reference_id = [];
        foreach ($orders as $key => $value) {
            if ($value->Shipping?->refrence_id)
                $reference_id[] = $value->Shipping->refrence_id;
        }

        if ($reference_id)
            SMSAShipingService::printInvoice($reference_id);
        else {

            session()->flash('error', __('translation.data.not.found'));
            return redirect()->back();
        }
    }

    public function syncShipingWithOrder(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'phone.*' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'country.*' => 'required|string',
            'address.*' => 'required|string',
            'name.*' => 'required|string',
            'city.*' => 'required|string',

        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $request->validate([
            'ids' => 'required',
        ]);
        $reference_id = [];
        $order_ids = decrypt(request()->ids);
        $orders = Order::find($order_ids);
        foreach ($orders as $key => $value) {
            // Handel Customer Models
            $customer =  SMSAShipingService::HandelCustomerModel($request, $value->id);
            $number = SMSAShipingService::shiping($customer);

            //  Create Order Shiping Iinstance
            OrderShiping::create(['order_id' => $value->id, 'shiping_type' => $request->type, 'number' => $number, "refrence_id" => $number]);
            $reference_id[] = $number;
        }
        $request->session()->flash('success', __('translation.the_orders_was_synced_sccuessfuly'));


        SMSAShipingService::printInvoice($reference_id);

        return redirect()->route('orders');
    }

    public function printInvocie($ids_key)
    {

        $reference_id = [];
        if (is_string($ids_key)) {
            $shiping_details =  Order::with('Shipping')->find($ids_key)->Shipping;
            $reference_id[] = $shiping_details->refrence_id;

            if ($shiping_details->id > -752)
                SMSAShipingServiceAPI::printAllLabel($reference_id);
            else
                SMSAShipingService::printInvoice($reference_id);
        } elseif (is_array($ids_key)) {
        }
    }

    public function cancel(Request $request)
    {
        // return request();

        $request->validate([
            'refrence_id' => 'required',
            'type' => 'required',
            'order_id' => 'required'
        ]);

        SMSAShipingService::cancel($request->refrence_id, $request->reason);
        OrderShiping::find($request->order_id)->delete();
        $request->session()->flash('success', __('translation.the_shiping_was_canceld'));
        return redirect()->back();
    }
}
