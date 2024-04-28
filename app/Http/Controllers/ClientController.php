<?php

namespace App\Http\Controllers;

use App\Events\BordcastToAllRepresetitve;
use App\Http\Resources\AreaService;
use App\Models\Area;
use App\Models\AreaServices;
use App\Models\Client;
use App\Models\ClientServicePrice;
use App\Models\ClientsTokens;
use App\Models\Order;
use App\Models\orderTracking;
use App\Models\SerialSetting;
use App\Models\Service;
use App\Models\SubArea;
use App\Traits\SendNotifcationWithFirebaseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;
use App\Traits\CreateUserWithFireBase;
use Exception;

class ClientController extends Controller
{
    use SendNotifcationWithFirebaseTrait, CreateUserWithFireBase;
    public function index()
    {
        return view('clients.index');
    }
    public function payment()
    {
        return view('clients.payment');
    }

    public function updateClientAccount(Request $request)
    {
        $fields = $request->validate([
            'fullname' => 'string',
            'email' => 'required_without:phone|email',
            'phone' => 'sometimes',
            'sub_area_id' => 'required|exists:sub_areas,id',
            'area_id' => 'required|exists:areas,id',
            'address' => 'required',
            'activity' => 'required',
            'name_in_invoice' => 'required',
            'bank_account_owner' => 'required',
            'bank_account_number' => 'required',
            'iban_number' => 'required',
            'civil_registry' => 'required',
        ]);
        try {
            $client = Client::find(auth()->user()->id);
            // update Valid Date                                    ######################
            $client = $client->update($fields);
            if ($client) {
                return response([
                    'status' => true,
                    'message' => 'Data updated successfully!',
                    'data' => $client,
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Something went wrong!'
                ]);
            }
        } catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => 'Something went wrong!'
            ]);
        }
    }

    public function addOrder(Request $request)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|exists:services,id',
            'sender_name' => 'required|string',
            'sender_phone' => 'required',
            'sender_area_id' => 'required|exists:areas,id',
            'sender_sub_area_id' => 'required|exists:sub_areas,id',
            'sender_address' => 'required',
            'receiver_name' => 'required|string',
            'receiver_phone_no' => 'required|String',
            'receiver_area_id' => 'required|exists:areas,id',
            'receiver_sub_area_id' => 'required|exists:sub_areas,id',
            'receiver_address' => 'required',
            // 'representative_id' => 'required|exists:representatives,id',
            'payment_method' => 'required|in:"on_sending", "on_receiving","balance"',
            'order_fees' => 'numeric|min:0',
            'police_file' => 'nullable',
            'receipt_file' => 'nullable',
            'note' => 'nullable',
            'number_of_pieces' => 'nullable',
            'order_weight' => 'required',
            'order_value' => 'required',
        ]);
        try {
            $Client = Client::find(auth()->user()->id);
             $status = $Client->update([
                "in_accounts_order" => 1,
                "client_id" => auth()->user()->id,
            ]);
            if ($request->police_file) {
                $police_file_url = '/images/police_file/' . "police_file-" . auth()->user()->id . '-' . time() . ".png";
                $path = public_path() . $police_file_url;
                Image::make(file_get_contents($request->police_file))->save($path);
                $police_file_path = $police_file_url;
            } else {
                $police_file_path = "";
            }
            if ($request->receipt_file) {
                $receipt_file_url = '/images/receipt_file/' . "receipt_file-" . auth()->user()->id . '-' . time() . ".png";
                $path = public_path() . $receipt_file_url;
                Image::make(file_get_contents($request->receipt_file))->save($path);
                $receipt_file_path = $receipt_file_url;
            } else {
                $receipt_file_path = "";
            }

            if ($Client->is_has_custom_price) {
                $validatedData['delivery_fees'] = (int) filter_var(ClientServicePrice::where('service_id', $request->service_id)->where('client_id', $Client->id)->first()->price, FILTER_SANITIZE_NUMBER_INT);
                $validatedData['total_fees'] =  $validatedData['order_fees'];
            } else {
                $validatedData['delivery_fees'] = (int) filter_var(service::find($request->service_id)->price, FILTER_SANITIZE_NUMBER_INT);
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
            $validatedData['client_id'] = auth()->user()->id;
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
            orderTracking::insertOrderTracking($order->id, __('translation.' . $order->status), " تم اضافه طلب جديد بواسطه  " . auth()->user()->fullname . " بتاريخ  " . $order->created_at, auth()->user()->fullname, auth()->user()->id, " تمت اضافه عنصر بواسطه  " . auth()->user()->fullname . 'في' . $order->created_at);
            if ($order) {
                // fire event when order Created     -------------------
                // event(new BordcastToAllRepresetitve('representative.' . $validatedData['receiver_area_id'] , __('translation.OrderMangemnt') , __('translation.added_New_OrderBy') ." ". auth()->user()->fullname, '' , $order->id));
                // Return Response Order Is Created    ------------------
                $Order = DB::table('orders_full_data')
                    ->where('client_id', '=', auth()->user()->id)
                    ->where('is_deleted', '=', '0')
                    ->where('id', $order->id)->first();
                return response([
                    'status' => true,
                    'message' => 'Order Created Successfully!',
                    'data' => $Order
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Something Went Wrong',
                ]);
            }
        } catch (Exception $e) {
            throw $e;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }
    public function EditOrder(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:orders,id',
            'service_id' => 'required|exists:services,id',
            'sender_name' => 'required|string',
            'sender_phone' => 'required',
            'sender_area_id' => 'required|exists:areas,id',
            'sender_sub_area_id' => 'required|exists:sub_areas,id',
            'sender_address' => 'required',
            'receiver_name' => 'required|string',
            'receiver_phone_no' => 'required|String',
            'receiver_area_id' => 'required|exists:areas,id',
            'receiver_sub_area_id' => 'required|exists:sub_areas,id',
            'receiver_address' => 'required',
            // 'representative_id' => 'required|exists:representatives,id',
            'payment_method' => 'required|in:"on_sending", "on_receiving","balance"',
            'order_fees' => 'numeric|min:0',
            'police_file' => 'nullable',
            'receipt_file' => 'nullable',
            'order_weight' => 'required',
            'order_value' => 'required',
            'note' => 'nullable',
        ]);
        try {
            //ensure order fees is 0 when payment method = balance
            // if ($validatedData['payment_method'] == "balance") {
            //     $validatedData["order_fees"] = 0;
            // }


            if ($request->police_file) {
                $police_file_url = '/images/police_file/' . "police_file-" . auth()->user()->id . '-' . time() . ".png";
                $path = public_path() . $police_file_url;
                Image::make(file_get_contents($request->police_file))->save($path);
                $police_file_path = $police_file_url;
            } else {
                $police_file_path = "";
            }
            if ($request->receipt_file) {
                $receipt_file_url = '/images/receipt_file/' . "receipt_file-" . auth()->user()->id . '-' . time() . ".png";
                $path = public_path() . $receipt_file_url;
                Image::make(file_get_contents($request->receipt_file))->save($path);
                $receipt_file_path = $receipt_file_url;
            } else {
                $receipt_file_path = "";
            }
            /*
            if (4 == $validatedData['service_id']) {
                $validatedData['delivery_fees'] = 20;
            } else if (5 == $validatedData['service_id']) {
                $validatedData['delivery_fees'] = 30;
            } else {
                $validatedData['delivery_fees'] = (int) filter_var(Area::find($request->sender_area_id)->fees, FILTER_SANITIZE_NUMBER_INT);
            } */


            //   $validatedData['representative_deserves'] = $validatedData['delivery_fees'] * env('REPRESENTATIVE_PERCENTAGE') / 100;

            //   $validatedData['company_deserves'] = $validatedData['delivery_fees'] - $validatedData['representative_deserves'];
            // $validatedData['is_payment_on_delivery'] = $validatedData['is_payment_on_delivery'] ? 1 : 0;
            $validatedData['order_date'] = date('Y-m-d H:i:s');
            // $validatedData['status'] = 'pending';
            $validatedData['police_file'] = $police_file_path;
            $validatedData['receipt_file'] = $receipt_file_path;
            $validatedData['client_id'] = auth()->user()->id;

            //$validatedData['tracking_number'] = orderTracking::generateUniqueTrackingNumber();

            //generate invoice no
            // $inv_no = SerialSetting::first()->inv_no;
            // SerialSetting::first()->update(["inv_no" => ($inv_no + 1)]);
            // $validatedData['invoice_sn'] = genInvNo($inv_no);

            // dd($validatedData);
            //  return $validatedData;
            $order = Order::find($validatedData['id']);
            if ($request->order_fees !== $order->order_fees) {
                $validatedData['delivery_fees'] = (int) filter_var(service::find($request->service_id)->price, FILTER_SANITIZE_NUMBER_INT);
                $validatedData['total_fees'] =  $validatedData['order_fees'];
                $Client = Client::find(auth()->user()->id);
                // sub old value and add new Fees
                $Client->account_balance = $Client->account_balance  - $order->total_fees + $validatedData['total_fees'];
                // $Client->account_balance =   $Client->account_balance + $request->order_fess;
                $Client->save();
            }

            $OrderUpdate = $order->update($validatedData);

            if ($order) {
                return response([
                    'status' => true,
                    'message' => 'Order updated successfully!',
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Something Went Wrong',
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
            // return response([
            //     'status' => false,
            //     'message' => 'Something Went Wrong'
            // ]);
        }
    }
    public function getOrder(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required|exists:orders,id'
        ]);
        try {
            //code...
            // $order = Order::with('service')->isDeleted()->find($validatedData['order_id']);
            $order = DB::table('orders_full_data')
                ->select("*")
                ->where('id', '=', $validatedData['order_id'])
                ->where('is_deleted', '=', '0')
                ->get();

            return response([
                "status" => true,
                "data" => $order,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }

    public function getClientOrders(Request $request)
    {
        $validatedData = $request->validate([
            'is_history_orders' => 'required|in:0,1'
        ]);
        try {
            // $orders = Order::isDeleted()->where('client_id', auth()->user()->id)->get();
            $orders = DB::table('orders_full_data')
                ->where('client_id', '=', auth()->user()->id)
                ->where('is_deleted', '=', '0')
                ->when($validatedData['is_history_orders'], function ($query) {
                    $query->whereIn('status', ['completed', 'delivered', 'returned']);
                }, function ($query) {
                    $query->whereIn('status', ['pending', 'pickup', 'inProgress']);
                })
                ->orderBy("id", "desc")
                ->get();
            return response([
                "status" => true,
                "data" => $orders
            ]);
        } catch (\Throwable $th) {
            throw $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }

    public function cancelOrder(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            Order::find($validatedData['order_id'])->update([
                'status' => 'canceled'
            ]);

            return response([
                "status" => true,
                "message" => "Order canceled successfully"
            ]);
        } catch (\Throwable $th) {
            // return $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }

    public function getClient(Request $request)
    {
        $validatedData = $request->validate([]);
        try {
            $client = Client::where('is_active', 1)->find(auth()->user()->id);
            if ($client) {
                return response([
                    "status" => true,
                    "data" => $client
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Something Went Wrong'
                ]);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }

    public function getAllServices(Request $request)
    {
        try {
            $services = Service::with('notes')->get();

            return response([
                "r" => $request,
                "status" => true,
                "data" => $services
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }
    public function getAllAreas(Request $request)
    {
        try {
            $areas = Area::with('subAreas')->get();

            return response([

                "status" => true,
                "data" => $areas
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }

    public function getAllAreas2(Request $request)
    {
        try {
            $areas = Area::with('subAreas')->whereIn('id', [0, 5])->get();

            return response([

                "status" => true,
                "data" => $areas
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }

    public function getAllAreasByServiceId(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'service_id' => 'required|integer',
                'is_sending' => 'nullable|integer',
                'is_resiving' => 'nullable|integer',
            ]);

            if ($validator->fails())
                return $validator->errors();

            $IsSending = $request->is_sending;
            $IsReseving = $request->is_resiving;
            // dd([$IsSending , $IsReseving]);

            if ($IsSending == null && $IsReseving == null) {
                $ids = AreaServices::where('service_id', $request->service_id)->get()->pluck('area_id');
            } elseif ($IsSending !== null && $IsReseving == null) {
                $ids = AreaServices::where('service_id', $request->service_id)->where('is_sending', 1)->get()->pluck('area_id');
            } elseif ($IsSending == null && $IsReseving !== null) {
                $ids = AreaServices::where('service_id', $request->service_id)->where('is_resiving', 1)->get()->pluck('area_id');
            } else {
                // dd($IsReseving);
                $ids = AreaServices::where('service_id', $request->service_id)->where('is_resiving', $IsReseving)->where('is_sending', $IsSending)->get()->pluck('area_id');
            }

            // return $ids;
            // $areas = Area::with('subAreas')->get();
            $data = Area::with('subAreas')->whereIn('id', $ids)->get();
            return response([
                "status" => true,
                "data" => $data,
            ]);
        } catch (\Throwable $th) {
            throw $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }

    public function getSubAreaByAreaId(Request $request)
    {
        $validatedData = $request->validate([
            'area_id' => 'required|exists:areas,id',
        ]);
        try {
            $sub_areas = SubArea::where('area_id', $validatedData['area_id'])->get();

            return response([
                "status" => true,
                "data" => $sub_areas
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }

    public function getallSubArea()
    {
        try {
            $sub_areas = SubArea::get();
            return response([
                "status" => true,
                "data" => $sub_areas
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response([
                'status' => false,
                'message' => 'Something Went Wrong'
            ]);
        }
    }


    public function addOrderWithApiKey(Request $request)
    {

       // dd($request);
        $validatedData = $request->validate([
            'service_id' => 'required',
        //     'sender_name' => 'required|string',
        //     'sender_phone' => 'required',
        //    'sender_area_id' => 'required|exists:areas,id',
        //    'sender_sub_area_id' => 'required|exists:sub_areas,id',
        //    'sender_address' => 'required',
            'receiver_name' => 'required',
            'receiver_phone_no' => 'required',
            'receiver_area_id' => 'required|exists:areas,id',
            'receiver_sub_area_id' => 'required|exists:sub_areas,id',
            'receiver_address' => 'required',
           //  'representative_id' => 'required|exists:representatives,id',
            //'payment_method' => 'required|in:"on_sending", "on_receiving","balance"',
            'order_fees' => 'required',
            'note' => 'nullable',
        ]);
        try {

            $apikey = $request->api_key;
            $clientsTokens = ClientsTokens::with('Client')
                ->select('client_id')
                ->where('api_key', $apikey )
                ->where('api_secret_token', $request->api_secret_token)->first();

            $Client  = $clientsTokens->Client;

          //  dd($Client);
    for ($i=0; $i < count($request->service_id); $i++) {


            $OrderData["sender_name"] = $Client->fullname;
            $OrderData["sender_phone"] = $Client->phone;
            $OrderData["sender_area_id"] = $Client->area_id;
            $OrderData["sender_sub_area_id"] = $Client->sub_area_id;
            $OrderData["sender_address"] = $Client->address;
            $OrderData["client_id"] = $Client->id;



            $OrderData["service_id"] = $request->service_id[$i];
            $OrderData["receiver_area_id"] = $request->receiver_area_id[$i];
            $OrderData["receiver_sub_area_id"] =$request->receiver_sub_area_id[$i];
            $OrderData["receiver_address"] =$request->receiver_address[$i];
            $OrderData["receiver_phone_no"] =$request->receiver_phone_no[$i];
            $OrderData["note"] =$request->note[$i];
            $OrderData['order_fees']=$request->order_fees[$i];



            $OrderData["payment_method"] = 'balance';
        if ($Client->is_has_custom_price) {
            $OrderData['delivery_fees'] = (int) filter_var(ClientServicePrice::where('service_id',  $OrderData["service_id"])->where('client_id', $Client->id)->first()->price, FILTER_SANITIZE_NUMBER_INT);
            $OrderData['total_fees'] =  $OrderData['order_fees'] - $OrderData['delivery_fees'];
        } else {
            $OrderData['delivery_fees'] = (int) filter_var(service::find( $OrderData["service_id"])->price, FILTER_SANITIZE_NUMBER_INT);
            $OrderData['total_fees'] =  $OrderData['order_fees'] - $OrderData['delivery_fees'];
        }

        $OrderData['order_date'] = date('Y-m-d H:i:s');
        $OrderData['status'] =  'pending' ;


        DB::transaction(function () use ($OrderData, $Client) {
            //generate invoice no
            $inv_no = SerialSetting::first()->inv_no;
            SerialSetting::first()->update(["inv_no" => ($inv_no + 1)]);
            $OrderData['invoice_sn'] = genInvNo($inv_no);
            $OrderData['tracking_number'] = orderTracking::generateUniqueTrackingNumber();
            $user_name =  $Client->name;
            $note = " تم اضافه الطلب بواسطه ($user_name)";

           // $order_id = Order::insertGetId($OrderData);
           $newOrder = Order::create($OrderData);
           $order_id = $newOrder->id;
            //insert order tracking
            orderTracking::insertOrderTracking($order_id,  __('translation.' . $OrderData['status']), $note, 'API', $Client->id);
            $Client->account_balance = $Client->account_balance +  $OrderData['total_fees'];
            $Client->save();
        });
    }
         //return $order_id;
         return response([
            'status' => true,
            'message' => 'successfully added'
        ]);
        } catch (\Throwable $th) {
            throw $th;
            // return response([
            //     'status' => false,
            //     'message' => 'Something Went Wrong'
            // ]);
        }
    }
    public function test()
    {
        // $this->AllRepresentiveNotifcation();
        $this->AddUserToFirebase('jksa.work.1@gmail.com12312', 'mohammed1234');
    }

    public function AreaStatic()
    {
        try {
            $Sql = "SELECT COUNT(orders.id)  as data , orders.sender_area_id, areas.name as label  from orders , areas WHERE areas.id = orders.sender_area_id and orders.client_id = ? GROUP BY orders.sender_area_id";
            $order = DB::select($Sql, [auth()->user()->id]);
            return response([
                "status" => true,
                "data" => $order,
            ]);
        } catch (Exception $e) {
            return $e;
            return response([
                "status" => true,
                "data" => 'something Went Worng',
            ]);
        }
    }

    public function serachInCleint(Request $request)
    {
        $prams = ($request->all());
        $clients = Client::where(function ($q) use ($prams) {
            foreach ($prams as $key => $value) {
                $q->orWhere($key, 'like', '%' . $value . '%')
                    ->orWhere($key, 'like',  '%' . $value)
                    ->orWhere($key, 'like',   $value . '%')
                    ->orWhere($key, 'like', $value);
            }
        })
            ->get();
        return  response()->json([
            'cleints' => $clients,
            'status' => 0,
        ], 200,);
    }

    public function OrdrsBelongToClient($id)
    {
        $orders = Order::where('client_id', $id)->get();
        return  response()->json([
            'orders' => $orders,
            'status' => 0,
        ], 200,);
    }

    public function ClientAjaxSearch(Request $request)
    {

        $search = $request->search;
        $prams = ['fullname', 'phone'];
        $employees = Client::when($search != null, function ($query) use ($prams, $search) {
            $query->orWhere(function ($q) use ($prams, $search) {
                foreach ($prams as  $key) {
                    $q->orWhere($key, 'like', '%' . $search . '%')
                        ->orWhere($key, 'like',  '%' . $search)
                        ->orWhere($key, 'like',   $search . '%')
                        ->orWhere($key, 'like', $search);
                }
            });
        })



            ->orderby('fullname', 'asc')->select('id', 'fullname')
            ->limit(5)->get();
        //  Return Reponose
        $response = array();
        foreach ($employees as $employee) {
            $response[] = array(
                "id" => $employee->id,
                "text" => $employee->fullname
            );
        }
        return response()->json($response);
    }
}
