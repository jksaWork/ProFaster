<?php

namespace App\Http\Livewire;

use App\Jobs\ExportNotificationJob;
use App\Models\Client;
use App\Models\ClientPay;
use App\Models\IssueClientStatement;
use App\Models\Order;
use App\Models\Service;
use App\Models\Setting;
use App\Traits\SendNotifcationWithFirebaseTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ClientAccountTransaction extends Component
{
    use SendNotifcationWithFirebaseTrait;
    public $client_id;
    public $from_date;
    public $to_date, $client, $client_name;
    public $total_amount_in = 0;
    public $total_amount_out = 0;
    public $client_current_account_balance = 0;
    public $checked_orders = [], $total_fees = 0;
    public $ClientIssue, $status;
    public $tax_value;

    // public $listeners  = ['fresh' => '$refresh'];
    // public function updatedCheckedOrders($value, $order_id)


    //     // dd($value , $order_id);
    //     // dd($this->checked_orders);
    //     // dd([$order_id ,$this->checked_orders]);
    //     // if ($value) {
    //     //     $this->total_fees += Order::find($order_id)->total_fees;
    //     // } else {
    //     //     $this->total_fees -= Order::find($order_id)->total_fees;
    //     // }
    //     // dd($this->total_fees);
    // }

    public function updatedClientIssue($key, $value)
    {
        dd($key, $value);
    }
    public function mount()
    {

        $this->tax_value = Setting::where('key', 'tax_precntage')->first();
    }
    public function hydrate()
    {
        $this->emit("select2");
        $this->total_amount_in = 0;
        $this->total_amount_out = 0;
    }


public function updatedOrderId($val)
    {
        $this->validate(['order_id' => 'required|exists:orders,tracking_number']);
       // $this->orders = Order::where(['client_id' => $val, 'client_id' => auth()->user()->id])->get();
    }

    public function getData()
    {
        $this->client_name = null;
        return $data = Order::where('id', null,)->IsDeleted()->whereIn('service_id', [1, 2] ,)->get();
    }

    public function PayToClient()
    {
        if ($this->client_id) {
            $ids = array_keys($this->checked_orders);
            $idsJson = json_encode($ids);
            Order::whereIn('id', $ids)->update(['is_collected' => 1]);
            $this->client_id = null;
            $this->total_fees = 0;
            $this->checked_orders = [];
            session()->flash('success', 'collection are done');
        } else {
            session()->flash('error', 'you must have a client');
        }
        // dd( , $this->total_fees);
    }


    public function getDataWithName()
    {
        $fromDate = $this->from_date;
        $ToDate = $this->to_date;
        $this->client_name = Client::find($this->client_id)->fullname ?? '';
         //dd($this->client_name);
         $data = Client::where('in_accounts_order', 1)
            ->with(['ServicePrice', 'Orders' => function ($q) use ($fromDate, $ToDate) {
                $q->where('is_collected', 0);
                $q->where('status', 'completed');
                // dd('hello');
                $q->when($fromDate, function ($query, $from_date) {
                    return $query->where('order_date', '>', $from_date);
                })->when($ToDate, function ($query, $ToDate) {
                    return $query->where('order_date', '<', $ToDate);
                });
            }]);

            if($this->client_id){
                $data=  $data->where('id' , $this->client_id);
            }
           return      $data->orderBy('updated_at', 'DESC')->get();
    }

    public function ExportIssueToClient($id)
    {
        try {
            DB::beginTransaction();
            $fromDate = $this->from_date;
            $ToDate = $this->to_date;

            $Client  = Client::with(['Orders' => function ($q) use ($fromDate, $ToDate) {
                $q->where('is_collected', 0);
                $q->where('status', 'completed');

                $q->when($fromDate, function ($query, $from_date) {
                    return $query->where('order_date', '>', $from_date);
                })->when($ToDate, function ($query, $ToDate) {
                    return $query->where('order_date', '<', $ToDate);
                });
            }])->where(['id' =>  $id, 'in_accounts_order' => 1])->first();
            // dd($Client);

            if ($Client->Orders ==  null) return;
            if ($Client) {
                $totalBlade = 0;
                $DeliveryFess = [];
                // $totalOfService = 0;
                $OrderDeliveryFess = 0;
                $total_cod_amount = 0;
                $total_service_charges = 0;
                foreach ($Client->Orders as $Order) {
                    if ($Order->service_id == 1) {
                        if ($Order->order_value != 0) {
                            $total_cod_amount += $Order->order_value;
                            $total_service_charges += $Order->service->price;
                        } else {
                            $total_service_charges += $Order->service->cod;
                        }
                        continue;
                    }
                    $total_service_charges += $Order->service->price;
                    // $OrderDeliveryFess += $Order->;
                    // $totalBlade += $Order->total_fees;
                    // // $totalOfService += $Order->total_fees - $Order->delivery_fees;
                    // if (isset($DeliveryFess[$Order->service_id])) {
                    //     $DeliveryFess[$Order->service_id] +=  (int) $Order->delivery_fees;
                    // } else {
                    //     $DeliveryFess[$Order->service_id] =  (int) $Order->delivery_fees;
                    // }
                }
                $isues_tax_value = $total_cod_amount * ($this->tax_value / 100);
                $total = ( ($total_cod_amount + $isues_tax_value)-$total_service_charges );
                $totalOfService = ($DeliveryFess[1] ?? 0) + ($DeliveryFess[2] ?? 0);
                $OrderArray = $Client->Orders->pluck('id');
                $OrdersTotalFess = $totalBlade - $OrderDeliveryFess;
                // dd($DeliveryFess , $totalOfService ,$totalBlade , $DeliveryFess[1] + $DeliveryFess[2] );
                // throw new Exception("Error Processing Request", 1);
                // dd($OrderArray);
                // Notification User
                dispatch(new ExportNotificationJob($Client));
                Order::whereIn('id', $OrderArray)->update(['is_collected' =>  1]);
                $DataForCreate = [
                    'total_shiping_type' => $DeliveryFess[2]  ?? 0,
                    'total_deleviry_fess' => $DeliveryFess[1] ?? 0,
                    'total_order_fess' => $totalBlade,
                    'total_service_fess' => $totalOfService,
                    'total_fess' => $total,
                    'orders_ids' => json_encode($OrderArray),
                    'status' => 'unpaid',
                    'client_id' => $Client->id,
                    'total_service_charges' =>  $total_service_charges,
                    'total_cod_amount' => $total_cod_amount,
                    'tax' => $isues_tax_value,
                ];
                // dd($DataForCreate);
                IssueClientStatement::create($DataForCreate);
                $Client->in_accounts_order = 0;
                $Client->save();
                // $this->sendFirebAseNotifcation('token' , 'title' , 'jksa');
                $this->emit('Success', __('translation.export_was_done_success'));
            } else {
                // $this->emit('ErrorException', __('translation.exception_on_export_issue'));
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $this->emit('ErrorException', __('translation.exception_on_export_issue'));
            $this->emit('ErrorException', $e->getMessage());
            session()->put('error', __('translation.error.exception'));
        }
    }

    public function ExportIssueToClientWithCheckBoxs()
    {
        foreach ($this->checked_orders as $ClientId) {
            $this->ExportIssueToClient($ClientId);
        }
    }

    public function render()
    {

        // dd($Client);
        if ($this->client_id  || $this->to_date  ||  $this->from_date) {
            $Client = $this->getDataWithName();
        } else {
            $Client = $this->getDataWithName();
        }
        $ServiceHeading = [
            1 => [
                '<div>
            <h4> COD Orders - Delivered </h4>
            <h4> الدفع عند الاستلام  - تم التوصيل  </h4> </div>  ',
                '<div>
            <h4> Orders - Delivered </h4>
            <h4> طلبات التوصيل - المدفوعه - تم التوصيل </h4> </div>  ',
            ],
            2 => '<div>
            <h4> Local shipping Orders </h4>
            <h4>  شحن الطلبات خارج المنطقة </h4> </div>',
            3 => '<div>

                 <h4> International shipping Orders </h4>
            <h4> الشحن الدوالى </h4> </div>',
            4 => '<div>
            <h4> Returned Orders </h4>

            <h4> استرجاع الطلبات من العميل </h4> </div>',

             5 => '<div>

            <h4>استرجاع الطلبات بعد محاولة التسليم  </h4> </div>',

        ];


        return view('livewire.client-account-transaction', [
            "clients" => $Client,
            'client_name' => $this->client_name,
            'Services' => Service::all(),
            'ServiceHeading'  => $ServiceHeading,
            'tax_value' => $this->tax_value,
        ]);
    }
}
