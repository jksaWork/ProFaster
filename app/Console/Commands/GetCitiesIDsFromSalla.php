<?php

namespace App\Console\Commands;

use App\Models\SallaCity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetCitiesIDsFromSalla extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'handel:cities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get City From Salla';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $merchantToken = 'ory_at_vMb2hdGZ2o2C-3Uyg1fNU4dKQ0tBMm5L-G-IiB2TEsc.aKS3WmcuL-T2eUONZIsvKgnylbiCMCFu0P1oon5GbP0';
        $url = "https://api.salla.dev/admin/v2/countries/1473353380/cities";
        $headers = [
            'Content-Type' => 'application/json', 
            'Authorization' => 'Bearer ' . $merchantToken,
        ];
        
        // Initialize an empty array to store all cities
        $allCities = [];
        
        // Set the initial page number
        $page = 1;
        
        do {
            info("Get Page Number " .  $page);

            // Fetch the data for the current page
            $response = Http::withHeaders($headers)
                            ->get($url, ['page' => $page]);
        
            // Check if the request was successful
            if ($response->successful()) {
                // Extract the data from the response
                $data = $response->json()['data'];
        
                // Append the cities from the current page to the array
                $allCities = array_merge($allCities, $data);
        
                // Increment the page number for the next request
                $page++;
            } else {
                // Log or handle the error if the request was not successful
                dd($response->json());
            }
        } while (!empty($data)); // Continue fetching pages until there is no more data
        
        // Process the fetched cities
        foreach ($allCities as $key => $city) {
            info("Save City Number" .  $key);
            SallaCity::create([
                "salla_id" => $city['id'], 
                "salla_name" => $city['name'], 
                "salla_name_en"=> $city['name_en'], 
                "country_id" => $city['country_id'], 
                'sub_area_id' => null, 
            ]);
        }
        
    }
}