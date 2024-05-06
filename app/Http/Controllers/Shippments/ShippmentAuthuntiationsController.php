<?php

namespace App\Http\Controllers\Shippments;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SallaMerchant;
use Exception;
use Illuminate\Http\Request;
use Salla\OAuth2\Client\Provider\Salla;

class ShippmentAuthuntiationsController extends Controller
{
    public function  getTokenWithCodeAndUpdateClientFilleds(Request $req)
    {
        info("Inter The Web Hock Code");
        // for dev -----------------------------
        $provider = new Salla([
            'clientId'     =>   'cbc3a856-8fd1-4452-9ccc-b7bd41081969' , #'c5e26ae228c097732386852c0194ade7', // The client ID assigned to you by Salla
            'clientSecret' =>  '7e4071235c11bd8bf81ad3d8914e8bf2' ,  #'470e3ce6a091ce4a43fe30be1792313c', // The client password assigned to you by Salla
            'redirectUri'  => 'https://salla.proofast.com/webhook' , // the url for current page in your service
        ]);
     
        info("Inter Affter Get Profile Id");


        // dd($provider);

        if (!isset($_GET['code']) || empty($_GET['code'])) {
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl([
                'scope' => 'offline_access',
                //Important: If you want to generate the refresh token, set this value as offline_access
            ]);
            header('Location: ' . $authUrl);
            exit;
        }

        // Try to obtain an access token by utilizing the authorisations code grant.
        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
            /** @var \Salla\OAuth2\Client\Provider\SallaUser $user */
            
            $user = $provider->getResourceOwner($token);


            $Client_json = json_encode($user->toArray());
           
          
            $Client = Client::where(['email' => $user->getEmail()])->first();

            if(!$Client){
                $Client = Client::create([
                    'fullname' => $user->getName() ,
                    'phone' => $user->getMobile(),
                    'area_id' => 1, 
                    'sub_area_id' => 1, 
                    'enable_salla_shippments' => true, 
                    'client_type' => 'salla_acount', 
                ]);
            }
                

            if($Client->enable_salla_shippments == false){
                return "This Client WasNot  Enabled Salla Feture";
            }

            SallaMerchant::updateOrCreate(
                ['merchant_id' => $user->toArray()['merchant']['id']],
                [
                    'client_id' => $Client->id, 
                    'access_token' =>$token->getToken() ,
                    'refresh_token' => $token->getRefreshToken() ,
                    'expired_date' => $token->getExpires(),
                 ]);
           
            return redirect()->to('https://s.salla.sa/apps');
        
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or merchant details.
            // show a error message to the merchant with good UI
            exit($e->getMessage());
        }catch(Exception $e){
            return $e;
        }
    }
}
