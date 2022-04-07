<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        // return response(['api_token' => $request->user()->api_token]);
        $user = $request->user();

        switch ($request->format) {
            case 'json':
                $resp = success($user);
                break;

            case 'plaintext':
                $resp = $user->api_token;
                break;

            default:
                $resp = view('index', compact('user'));
                break;
        }
        return $resp;
    }

    public function redirect(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => config('oauth.client_id'),
            'redirect_uri' => config('oauth.callback_uri'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);

        return redirect()->to(config('oauth.oauth_auth_url') . '?' . $query);
    }

    public function callback(Request $request)
    {
        $state = $request->session()->pull('state');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );

        $http = new Client();

        $authorize = $http->post(config('oauth.oauth_token_url'), [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => config('oauth.client_id'),
                'client_secret' => config('oauth.client_secret'),
                'redirect_uri' => config('oauth.callback_uri'),
                'code' => $request->code,
            ],
        ])->getBody();
        $authorize = json_decode($authorize);

        $oauth_user = $http->get(config('oauth.oauth_user_url'), [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $authorize->access_token,
            ],
        ])->getBody();
        $oauth_user = json_decode($oauth_user);

        $user_sql = User::where('email', $oauth_user->email);
        $user = $user_sql->first();

        if (is_null($user)) {
            $name = $oauth_user->name;
            $email = $oauth_user->email;
            $email_verified_at = $oauth_user->email_verified_at;
            $email_verified_at = Carbon::parse($email_verified_at);
            $api_token = Str::random(50);
            $user = User::create(compact('name', 'email', 'email_verified_at', 'api_token'));

            if (app()->environment() == 'local') {
                $user->api_token = $email;
                $user->save();
            }
        } else {
            if ($user->name != $oauth_user->name) {
                User::where('email', $oauth_user->email)->update([
                    'name' => $oauth_user->name
                ]);
            }
        }

        auth()->loginUsingId($user->id, true);

        return redirect()->route('index');
    }

    public function reset()
    {
        return view('password.reset');
    }

    public function setup_password(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        User::find(auth()->id())->update(['password' => Hash::make($request->password)]);

        return redirect()->route('main.index');
    }

    public function confirm()
    {
        return view('password.confirm');
    }

    public function confirm_password(Request $request)
    {
        $request->validate($this->password_rules());

        $request->session()->put('auth.password_confirmed_at', time());

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    protected function password_rules()
    {
        return [
            'password' => 'required|password',
        ];
    }

    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/';
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->flush();
        return redirect()->route('index');
    }
}
