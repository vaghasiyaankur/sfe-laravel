<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OutController extends Controller
{



    public function redirect($provider)
    {
      // return Socialite::driver($provider)->redirect();
   
          // return response()->json([
          //     'url' => Socialite::driver($provider)->stateless()->redirect()->getTargetUrl(),
          // ]);
          
      // dd(Socialite::driver($provider));
      // dd(Socialite::driver('twitter')->stateless()->setScopes(config('services.twitter.scopes'))->redirect());
      // dd(Socialite::driver($provider));
      if($provider == 'twitter') $url =  Socialite::driver('twitter')->setScopes(['users.read', 'tweet.read
      '])->redirect()->getTargetUrl();
      else $url =  Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
      // $url = 'https://twitter.com/i/oauth2/authorize?client_id='.env('TWITTER_ID').'&redirect_uri='.env('TWITTER_URL').'&scope=users.read%20tweet.read&response_type=code&state=TMAOCkhKHvzoxvHBa6bRkjVnJ3IDsoA9W3JhGW14&code_challenge=YzS2y85J6dfkupsnapaevU1xSL9vnQ064yYsYQzP5qw&code_challenge_method=S256';

      return response()->json([
        'url' => $url
      ]);
    }


    public function callback($provider)
    {
      if($provider == 'twitter')  $user = Socialite::driver($provider)->stateless()->setScopes(config('services.twitter.scopes'))->user();
      else $user = Socialite::driver($provider)->stateless()->user();

      /* Success Response and data retrieved from integration */
      return response()->json([
        'user' => $user
      ]);

    }
}