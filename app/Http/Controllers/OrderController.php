<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientServicePrice;
use App\Models\Order;
use App\Models\orderTracking;
use App\Models\Representative;
use App\Models\SerialSetting;
use App\Models\Service;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;


class OrderController extends Controller
{
    public function index()
    {
        return view('orders.index');
    }


    public function neww()
    {
        return view('orders.new');


    }



    public function printInvoice($invoice_sn)
    {
        $order = Order::find($invoice_sn);
        return view("orders.invoice", [
            "order" => $order,
        ]);
    }
    public function importCSV()
    {
        return view('orders.importCSV');
    }

    public function orderTracking($id = null)
    {
        return view('orders.tracking', compact('id'));
    }

    public function show($id)
    {
        $order = Order::find($id);
        $data = $this->GetTracking($order->tracking_number);

        //dd( $data);
        return view('orders.info', compact('order','data'));
    }

    public function printInvoices(Request $request)
    {
        $ArrayIds =  explode(',',  $request->ids);
        $Orders = Order::whereIn('id', $ArrayIds)->get();
        //$order = Order::first();
        return view('orders.invoices', compact('Orders'));
    }

    public function getOrderById($id)
    {
        $validaotr = validator(compact('id'), [
            'id' => 'required',
        ]);
        if ($validaotr->fails()) return $validaotr->errors();
        // Return Order If Exist ---------------------
        try {
            $order = DB::table('orders_full_data')
                ->select("*")
                ->where('id', '=', $id)
                ->where('is_deleted', '=', '0')
                ->get();
            // response ------------------------------
            return response([
                "status" => true,
                "data" => $order,
            ]);
        } catch (Exception $ex) {
            return $ex;
        }
    }

    public function getOrderBytrackingId($id)
    {
        $validaotr = validator(compact('id'), [
            'id' => 'required',
        ]);
        if ($validaotr->fails()) return $validaotr->errors();
        // Return Order If Exist ---------------------
        try {
            $order = DB::table('orders_full_data')
                ->select("*")
                ->where('tracking_number', '=', $id)
                ->where('is_deleted', '=', '0')
                ->get();
            // response ------------------------------
            return response([
                "status" => true,
                "data" => $order,
            ]);
        } catch (Exception $ex) {
            return $ex;
        }
    }
    public function  SaveOrder(Request $request)
    {
      //return ($request);
        $validatedData = $request->validate([
            'service_id' => 'required|exists:services,id',
            'client_id' => 'required|exists:clients,id',
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
              'representative_id' => 'nullable|exists:representatives,id',
            // 'payment_method' => 'required|in:"on_sending", "on_receiving","balance"',
            'order_fees' => 'numeric|min:0',
            'police_file' => 'nullable|mimes:jpg,jpeg,png,bmp,svg,webp,pdf',
            // 'is_payment_on_delivery' => 'nullable|in:0,1',
          //  'is_collected' => 'required',
            'number_of_pieces' => 'required',
            'order_weight' => 'required',
            'order_value' => 'required',
        ]);

        try {
            //ensure order fees is 0 when payment method = balance
            // if ($validatedData['payment_method'] == "balance") {
            //     $validatedData["order_fees"] = 0;
            // }



            if ($request->hasFile('police_file')) {
                $police_file_path = $request->store('orders');
            } else {
                $police_file_path = "";
            }

            $Client = Client::find($request->client_id);

             $status = $Client->update([
                "in_accounts_order" => 1,
                "client_id" => auth()->user()->id,
            ]);




            if ($Client->is_has_custom_price) {
                $validatedData['delivery_fees'] = (int) filter_var(ClientServicePrice::where('service_id', $request->service_id)->where('client_id', $Client->id)->first()->price, FILTER_SANITIZE_NUMBER_INT);
                $validatedData['total_fees'] =  $validatedData['order_fees'] - $validatedData['delivery_fees'];
            } else {
                $validatedData['delivery_fees'] = (int) filter_var(Service::find($request->service_id)->price, FILTER_SANITIZE_NUMBER_INT);
                $validatedData['total_fees'] =  $validatedData['order_fees'] - $validatedData['delivery_fees'];
            }

            // $validatedData['delivery_fees'] = (int) Area::find($this->sender_area_id)->fees;
            // $validatedData['total_fees'] = $validatedData['delivery_fees'] + $validatedData['order_fees'];
            $validatedData['order_date'] = date('Y-m-d H:i:s');
            $validatedData['status'] = 'pickup';
            $validatedData['police_file'] = $police_file_path;
            $validatedData['note'] = $request->orede_Note;


            DB::transaction(function () use ($validatedData, $Client, $request) {
                //generate invoice no
                $inv_no = SerialSetting::first()->inv_no;
                SerialSetting::first()->update(["inv_no" => ($inv_no + 1)]);
                $validatedData['invoice_sn'] = genInvNo($inv_no);
                $validatedData['tracking_number'] = orderTracking::generateUniqueTrackingNumber();
                $user_name = auth()->user()->name;
                $note ="في الطريق للاستلام من المرسل "; //" تم اضافه الطلب من قبل الاداره بواسطه ($user_name)";
                if ($request->representative_id) {
                    $representative_name = Representative::find($validatedData['representative_id'])->fullname;
                    $note .= "and assigned to representative ($representative_name)";
                } else {
                    // $request->representative_id = null;
                }

               // $order_id = Order::insertGetId($validatedData);
               $newOrder = Order::create($validatedData);
               $order_id = $newOrder->id;
                //insert order tracking
                orderTracking::insertOrderTracking($order_id,  __('translation.' . $validatedData['status']), $note, 'admin', auth()->user()->id);
                $Client->account_balance = $Client->account_balance +  $validatedData['total_fees'];
                $Client->save();
            });

            // representative deserves calculation
            // $representative_deserves_calculation_method = Setting::where('key', 'representative_deserves_calculation_method')->first()->value;
            // if ($representative_deserves_calculation_method == 'percentage') {
            //     $representative_percentage = Setting::where('key', 'representative_percentage')->first()->value;
            //     $representative_deserves = $validatedData['delivery_fees'] * ($representative_percentage / 100);
            //     RepresentativeOrdersPercentage::create(
            //         ['representative_id' => $validatedData['representative_id'], 'order_id' => $order_id, 'deserve' => $representative_deserves]
            //     );
            // }

            session()->flash('success', __('translation.item.created.successfully'));
            return redirect()->route('orders');
        } catch (\Throwable $th) {
            session()->flash('error', __('translation.error.exception'));
            dd($th);
        }
    }


    public function GetTracking($searchText)
    {
        $data = DB::table('orders')
            ->select('order_trackings.*')
            ->join('order_trackings', 'order_trackings.order_id', '=', 'orders.id')
            ->where('orders.tracking_number', $searchText)
            ->orderBy('id', 'desc')
            ->get();

        if ( $data->isEmpty()) {
            session()->flash('error', __('translation.data.not.found'));
            return;
        }

        foreach ( $data as $item) {
            if (isset($item->date)) {
                $item->date = date('Y-m-d H:i:s', strtotime($item->date));
            }
        }

        $ordersShipping = Order::with('Shipping')->where('id', $data->first()->order_id)->get();
        if ($ordersShipping && $ordersShipping->first()->Shipping) {
            // Initialize cURL session
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://www.smsaexpress.com/ae/ar/trackingdetails?tracknumbers%5B0%5D=290638225902');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $html = curl_exec($curl);
            if ($html === false) {
                $error = curl_error($curl);
                //   dd("cURL Error: $error");
            }

            curl_close($curl);
            //dd($html);

            // Parse the HTML and extract tracking data
            $objectData = $this->parseHTML($html); // Assume parseHTML is a method that parses the HTML and returns tracking data as an array

            // Merge order tracking data with scraped data
            $combinedData = $data->merge($objectData);
        } else {
            $combinedData = $data;
        }

        $combinedData = $combinedData->map(function ($item) {
            $other = isset($item->status) ? ' , ' . $item->status : ' , ' . $item["location"];
            $date = isset($item->date) ? $item->date : $item["date"];
            $note = isset($item->note) ? $item->note : $item["description"];
            $formattedDate = $date ? date('Y-m-d H:i:s', strtotime($date)) : null;
            return ['date' => $formattedDate, 'note' => $note . $other];
        });

        $combinedData = $combinedData->filter(function ($item) {
            return isset($item['date']) && !empty($item['date']);
        });

        return $combinedData->sortByDesc('date')->values()->all();
    }

    private function parseHTML($html)
    {
        $crawler = new Crawler($html);

        // Mapping of Arabic month names to their numerical equivalents
        $months = [
            'يناير' => '01',
            'فبراير' => '02',
            'مارس' => '03',
            'أبريل' => '04',
            'مايو' => '05',
            'يونيو' => '06',
            'يوليو' => '07',
            'أغسطس' => '08',
            'سبتمبر' => '09',
            'أكتوبر' => '10',
            'نوفمبر' => '11',
            'ديسمبر' => '12',
        ];

        $results = [];

        $crawler->filter('.tracking-timeline .row')->each(function (Crawler $rowNode) use (&$results, $months) {
            $dateNode = $rowNode->filter('.date-wrap h4');
            $dateText = $dateNode->count() > 0 ? trim($dateNode->text()) : 'Unknown Date';

            // Attempt to reformat the date
            $dateParts = explode('-', $dateText);
            if (count($dateParts) == 3) {
                $day = $dateParts[0];
                $monthName = trim($dateParts[1]);
                $year = $dateParts[2];

                $month = array_key_exists($monthName, $months) ? $months[$monthName] : 'Unknown Month';
                $date = $day . '-' . $month . '-' . $year; // Reformat date as DD-MM-YYYY
            } else {
                $date = $dateText; // Use original text if parsing fails
            }

            $rowNode->filter('.tracking-details .trk-wrap')->each(function (Crawler $node) use ($date, &$results) {
                $timestamp = $node->filter('.trk-wrap-content-left span')->count() > 0 ? trim($node->filter('.trk-wrap-content-left span')->text()) : 'Unknown Timestamp';
                $location = $node->filter('.trk-wrap-content-right span')->count() > 0 ? trim($node->filter('.trk-wrap-content-right span')->text()) : 'Unknown Location';
                $description = $node->filter('h4')->count() > 0 ? trim($node->filter('h4')->text()) : 'Unknown Description';
                $description = str_replace('سمسا', 'بروفاست', $description);




                $dateTimeString = $date . ' ' . $timestamp;
                $dateTimeString = str_replace('مساء', 'PM', $dateTimeString);
                $dateTimeString = str_replace('صباحا', 'AM', $dateTimeString);
                $dateTime = \DateTime::createFromFormat('d-m-Y h:i A', $dateTimeString);
                 $dateString = $dateTime->format('Y-m-d H:i:s');

                $results[] = [
                    'date' => $dateString,
                    'timestamp' => $timestamp,
                    'location' => $location,
                    'description' => $description,
                ];
            });
        });

        return $results;
    }

}
