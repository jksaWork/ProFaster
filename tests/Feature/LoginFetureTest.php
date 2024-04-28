<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginFetureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/login');
        $response->assertStatus(200); //status
        $response->assertSee(__('translation.login'));
        $response->assertSee(__('translation.email'));
        $response->assertSee(__('translation.password'));
        $response->assertSee(__('translation.remember.me'));
        // $response->assertSee(__('translation.jksa'));
    }

    public function test_example_2(){
        $user = User::first();
        // dd($user);
        // $this->Accting
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
