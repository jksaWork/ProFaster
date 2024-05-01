<?php

use Alhoqbani\SmsaWebService\Models\Shipment;
use Alhoqbani\SmsaWebService\Smsa;
use App\Http\Controllers\Api\WhatServicesControll;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\Permission\RoleController;
use App\Http\Controllers\Permission\UserController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientIssuesController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FireBaseNotificationHistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HelpCenterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PrivacyPolicy;
use App\Http\Controllers\RegistrationRequestToBoth;
use App\Http\Controllers\Select2Controller;
use App\Http\Controllers\SubAreaController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceToArea;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SyncShipingWithOrderController;
use App\Http\Livewire\AddOrder;
use App\Http\Livewire\ClientAccountDetials;
use App\Http\Livewire\ClientsPay;
use App\Http\Livewire\ClientStatementIsues;
use App\Http\Livewire\MultiOrderMangement;
use App\Http\Livewire\ReprestiveOrderSearch;
use App\Jobs\ExportNotificationJob;
use App\Mail\ExportNotificationMail;
use App\Models\Client;
use App\Models\OrderShiping;
use App\Models\Service;
use App\Models\Setting;
use App\Services\Shiping\SMSAShipingService;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Controllers\SendEmailController;
use App\Http\Controllers\Shippments\ShipmentsController;
use App\Http\Controllers\Shippments\ShippmentAuthuntiationsController as ShippmentsShippmentAuthuntiationsController;
use App\Http\Middleware\VerifyCsrfToken;

Route::get('send-email', [SendEmailController::class, 'index']);
// Route::get('/', function () {
//     return view('welcome');
// });


/*--------------------------------------------------------------------------
| change language route
|--------------------------------------------------------------------------*/

Route::get('webhook', [ShippmentsShippmentAuthuntiationsController::class , 'getTokenWithCodeAndUpdateClientFilleds']);
Route::post('webhook2', [ShipmentsController::class , 'webhock2'])
->withoutMiddleware(VerifyCsrfToken::class);


Route::get('webhook2', [ShipmentsController::class , 'webhock2'])
->withoutMiddleware(VerifyCsrfToken::class);

Route::get('test' , [ShipmentsController::class , 'test']);


Route::get('printPDF-invoices/{orderId}', [ShipmentsController::class, 'printPDFInvoices'])->name('printPDF.invoices');