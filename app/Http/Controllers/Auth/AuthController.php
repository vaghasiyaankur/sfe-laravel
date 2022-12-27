<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Socialite;

class AuthController extends Controller
{


    public function __construct()
    {

    }

  // note if you are using sign by apple you need this
    public function AppleCode(Request $r, $provider)
    {
        // you can get apple code or finish all the process once
        return redirect($r->url() . '?code=' . $r->code);
    }

    public function SocialSignup(Request $r, $provider)
    {
        $validator = Validator::make($r->all(), [
            'code' => 'nullable|string',
            'hash' => 'nullable|string',
            'otp' => 'nullable|numeric',
            'token' => 'nullable|string',
            'secret' => 'nullable|string',

        ]);
        if ($validator->fails()) {
            return [
                'message' => 'Incorrect Data Posted',
                'status' => 445,
            ];
        }

        $hash = $r->hash ?? null;
        $hashuser = Cache::get($hash);
        if ($hashuser) {
            return $this->SocialSignupNext($r, $hashuser);
        }
        try {
            // Socialite will pick response data automatic
            $user = Socialite::driver($provider)->stateless()->user();
           $token = $user->token ?? null;
            $refreshToken = $user->refreshToken ?? null;
            $expiresIn = $user->expiresIn ?? null;
            $tokenSecret = $user->tokenSecret ?? null;
            $id = $user->id ?? $user->getId();
            $nickname = $user->nickname ?? $user->getNickname();
            $firstName = $user->name->firstName ?? null;
            $lastName = $user->name->lastName ?? null;
            $name = $user->name ?? $firstName . ' ' . $lastName ?? null;
            $email = $user->getEmail();
            $profileImage = $user->getAvatar();

             $data =  [
                'name' => $name,
                'nickname' => $nickname,
                'profileImage' => $profileImage,
                'username' => '',
                'email' => $email,
                'provider' => $provider,
                'provider_id' => $id,
                'token' => $token,
                'tokenSecret' => $tokenSecret,
                'refreshToken' => $refreshToken,
                'expiresIn' => $expiresIn,

            ];
        // this is optional can be skip you can return your user data from here
        if($email){

        return $this->SocialSignupNext($r, $data);

        }

        } catch (\Throwable $th) {
            logger($th);
        }

        return [
                'message' => 'Unknow Error',
                'status' => 445,
            ];
    }


    public function SocialSignupNext($request, $userdata)
    {
        $email = $this->xlean($userdata['email']);
        $provider = $this->clean($userdata['provider']);
        $provider_id = $this->clean($userdata['provider_id']);
        $name = $this->nlean($userdata['name']);
        $usr = User::where('email', $email)->get();

        $user =  $usr->where('provider', $provider)
            ->where('provider_id', $provider_id)
            ->first();

        if ($user) {
            return $this->SocialLogin($request, $user);
        }
        $user = $usr->first();
        if ($user) {
            $user->update([

                'provider' => $provider,
                'provider_id' => $provider_id,

            ]);
            return $this->SocialLogin($request, $user);
        }
        $u =  User::create([
            'name' => $name,
            'email' => $email,
            'provider' => $provider,
            'provider_id' => $provider_id,

        ]);
        // this is optional can be skip you can return your user data from here
        return $this->SocialLogin($request, $u);
    }



    public function SocialLogin($r, $user)
    {

        $hashid =  Str::random(12);

        // to verify additional security
        if ($user->google2fa_secret && !$this->mlean($r->otp)) {
            Cache::put($hashid, $user, now()->addMinutes(15));
            return [
                'message' => 'Unauthorized',
                'status' => 444,
                'hash' => $hashid
            ];
        }
        // check 2fa
        if ($this->mlean($r->otp)) {
            $g = \Google2FA::verifyKeyNewer(
                $user->google2fa_secret,
                ($this->mlean($r->otp)),
                $user->google2fa_ts
            );
            if (!$g) {
                return [
                    'message' => '2FA Expired Or Incorrect Code',
                    'status' => 445
                ];
            } else {
                $user->update([

                    'google2fa_ts' => $g

                ]);
                // optional incase you are using passport oAuth
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();
                return [
                    'u' => [
                        'data' => $tokenResult->accessToken,
                        'user' => $user
                    ]
                ];
            }
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();

        return [
            'u' => [
                        'data' => $tokenResult->accessToken,
                        'user' => $user
                    ]
        ];
    }


}