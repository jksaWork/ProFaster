<?php

namespace App\Http\Controllers;

use App\Http\Livewire\ClientStatementIsues;
use App\Models\Client;
use App\Models\IssueClientStatement;
use App\Models\IssuePhotos;
use App\Models\Order;
use App\Models\Service;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

class ClientIssuesController extends Controller
{
    public function showIssue($id)
    {
        $ClientStatementIsues = \App\Models\IssueClientStatement::with('Photos')->find($id);
        $Orders = Order::whereIn('id', $ClientStatementIsues->orders_ids)->get();
        
        
       //   $Orderss = Order::whereIn('status', [ 'pickup' , 'inProgress'])->get();
          
         //  $client1 = Client::with('ServicePrice')->where('status', $Orderss[0]->client_id)->first();
        $client = Client::with('ServicePrice')->where('id', $Orders[0]->client_id)->first();
        $Services  = Service::get();
        $ServicePrice =$client->is_has_custom_price ? $client->ServicePrice : $Services;
        // $cleint = Client::where('id' , 1)->first();
        // dd($ServicePrice);
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
            <h4> Returned Orders by the Client </h4>
          
            <h4> استرجاع الطلبات من العميل </h4> </div>',
            
             5 => '<div>
                      <h4> Retrieving orders after unsuccessful delivery </h4>

            <h4>استرجاع الطلبات بعد محاولة التسليم  </h4> </div>',
            
        ];

        // return $Orders;
        return view('clients.isues', compact('Orders', 'ServicePrice',  'ClientStatementIsues', 'ServiceHeading' ,'id', 'Services', 'client'));
    }
    public function StatusIssue($id)
    {
        $IssueClientStatement = IssueClientStatement::find($id);
        $IssueClientStatement->status =  $IssueClientStatement->status == 'paid' ? 'unpaid' : 'paid';
        $IssueClientStatement->save();
        return redirect()->back();
    }

    public function UploadFiles(Request $request, $id)
    {
        // return $request;
        try{
        $name = $request->file->getClientOriginalName();
        // dd($name);
        $moved = $request->file->move(public_path("issue/{$id}"), $name);
        IssuePhotos::create([
            'issue' => $id,
            'photo' => $name,
        ]);
        }catch(Exception $e){
        return redirect()->back();
       }
        return redirect()->back();
    }
    public function ShowImage($id)
    {
        try {
            $IssuePhotos  = IssuePhotos::find($id);
            $Photo = $IssuePhotos->getRawOriginal('photo');
            $path = public_path("issue/{$IssuePhotos->issue}/{$Photo}");
            return response()->file($path);
        } catch (Exception $e) {
            return redirect()->back();
        }
    }

    public function downloadImage($id)
    {
        try {
            $IssuePhotos  = IssuePhotos::find($id);
            $Photo = $IssuePhotos->getRawOriginal('photo');
            $path = public_path("issue/{$IssuePhotos->issue}/{$Photo}");
            $headers = ['Content-Type: image/jpeg'];
            return response()->download($path, $Photo, $headers);
        } catch (Exception $e) {
            return redirect()->back();
        }
    }

    public function DeletImage($id)
    {
        try {
            $IssuePhotos  = IssuePhotos::find($id);
            $Photo = $IssuePhotos->getRawOriginal('photo');
            $path = public_path("issue/{$IssuePhotos->issue}/{$Photo}");
            unlink($path);
            $IssuePhotos->delete();
            return redirect()->back();
        } catch (Exception $e) {
            return redirect()->back();
        }
    }
}
