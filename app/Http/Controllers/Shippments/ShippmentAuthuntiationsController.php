<?php

namespace App\Http\Controllers;

use App\Events\NewCleintInitApp;
use App\Models\Client;
use Exception;
use Illuminate\Http\Request;
use Salla\OAuth2\Client\Provider\Salla;

class ShippmentAuthuntiationsController extends Controller
{
    public function  getTokenWithCodeAndUpdateClientFilleds(Request $req)
    {
        // for dev -----------------------------
        $provider = new Salla([
            'clientId'     =>   '7fb2038a-e26e-4244-8d5a-343354f21b8d' , #'c5e26ae228c097732386852c0194ade7', // The client ID assigned to you by Salla
            'clientSecret' =>  '5e0396f5884611a73db7cc71732d2f11' ,  #'470e3ce6a091ce4a43fe30be1792313c', // The client password assigned to you by Salla
            'redirectUri'  => 'https://salla.proofast.com/webhock' , // the url for current page in your service
        ]);
     
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

            $Client = Client::updateOrCreate(
                ['merchant_id' => $user->toArray()['merchant']['id']],
                [
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
