<?php

namespace App\Listeners;

use App\Models\Order;
use Barryvdh\DomPDF\PDF;
use HttpClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

class UpdateShippemntsEventListner
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        info("Info From After No Listener");
        
        $shipment_id = $event->sallaOrder->shipment_id;


        $headers = [
            'Content-Type' => 'application/json', 
            'Authorization' => 'Bearer ' . $event->sallaMerchant->access_token,
         ];
         
         $file = $this->getFileInvoiceName($event->order->id);

         $body = [
            "order_id" =>  $event->sallaOrder->salla_order_id,
            "tracking_link"=> "https://api.shipengine.com/v1/labels/498498496/track",
            "shipment_number"=> "$shipment_id",
            "tracking_number"=>  $event->sallaOrder->tracking_number,
            "status"=> 'in_progress',
            "pdf_label"=> $file,  # "https://api.shipengine.com/v1/downloads/10/F91fByOB-0aJJadf7JLeww/label-63563751.pdf",
            "cost"=> $event->order->total_fees, 
            "status_note" => "Order Status Change By Faster Api"
         ];

         
        $response = Http::withHeaders($headers)
            ->withBody(json_encode($body), 'application/json')  
            ->put("https://api.salla.dev/admin/v2/shipments/{$shipment_id}");

        info('-----------request body ---------------');
        
        info($response->body());

        if($response->status() != 200){
            dd($response->object());
        } 
        
    }



    public function getFileInvoiceName($id)
    {
        $Orders = [Order::find($id)];

        // Load the blade file content into a variable
        $html = view('orders.invoices', compact('Orders'))->toArabicHTML();

        // Generate PDF from HTML content
        $pdf = PDF::loadHTML($html);
        // Save the PDF to a file
        $filename = 'pdfs/invoices_' .date('y_m_d_h_i_s'). ' _.pdf';

        $pdfFilePath = public_path($filename);
        $pdf->save($pdfFilePath);

        return $filename;
    }
}
