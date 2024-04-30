<?php

namespace App\Listeners;

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

        dd($event);
        $headers = [
            'Content-Type' => 'application/json'
         ];
         $body = [
            "order_id" =>  $event->sallaOrder->order_id,
            "tracking_link"=> "https://api.shipengine.com/v1/labels/498498496/track",
            "shipment_number"=> $event->sallaOrder->shipment_number,
            "tracking_number"=>  $event->sallaOrder->tracking_number,
            "status"=> $event->order->status,
            "pdf_label"=> "https://api.shipengine.com/v1/downloads/10/F91fByOB-0aJJadf7JLeww/label-63563751.pdf",
            "cost"=> 40
         ];

         
        $response = Http::withHeaders($headers)
            ->withBody(json_encode($body), 'application/json')  
            ->post('https://api.salla.dev/admin/v2/shipments/');

        dd($response->object());
    }
}
