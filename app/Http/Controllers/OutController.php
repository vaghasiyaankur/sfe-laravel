<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OutController extends Controller
{


    public function __construct()
    {

    }


    public function redirect($provider)
    {
      return Socialite::driver($provider)->stateless()->redirect();

    }


    public function callback($provider)
    {
      $user = Socialite::driver($provider)->stateless()->user();

      /* Success Response and data retrieved from integration */
      dd($user);

    }
}