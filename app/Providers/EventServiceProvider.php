<?php

namespace App\Providers;

use App\Events\SendNotifcationWithFireBase;
use App\Events\BordcastToAllRepresetitve;
use App\Events\UpdateShippemntsEvent;
use App\Listeners\SendNotifcationWithFireBaseListner;
use App\Listeners\ListenrBordcastToAllRepresetitve;
use App\Listeners\UpdateShippemntsEventListner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SendNotifcationWithFireBase::class => [
            SendNotifcationWithFireBaseListner::class
        ],
        BordcastToAllRepresetitve::class => [
            ListenrBordcastToAllRepresetitve::class ,
        ], 
        UpdateShippemntsEvent::class => [
            UpdateShippemntsEventListner::class, 
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
