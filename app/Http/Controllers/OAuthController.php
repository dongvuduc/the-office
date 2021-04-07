<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class OAuthController extends Controller
{
    public function getAccessToken(){
        return session('access_token');
    }

    public function getRefreshToken(){
        return session('refresh_token');
    }

    // LOGIN: Chuyển trang đăng nhập sang Server OAuth2 để user xác thực
    public function login(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => env('CLIENT_ID'),
            'redirect_uri' => env('APP_URL'). '/callback',
            'response_type' => 'code',
            'scope' => 'import',
            'state' => $state,
        ]);

        return redirect(env('SERVER_URL').'/oauth/authorize?'.$query);
    }

    // Làm mới Token đã hết hạn
    public function refreshtoken(){
        $response = Http::asForm()->post(env('SERVER_URL').'/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getRefreshToken(),
            'client_id' => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'),
            'scope' => 'import',
        ]);
        if($response->ok()){
            session()->put($response->json());
            return redirect('user');
        }else{
            return redirect('/');
        }
    }

    // Sau khi User đăng nhập Server thành công
    public function callback(Request $request){
        if($request->error){
            return redirect('/');
        }

        // kiểm tra dữ liệu
        $state = $request->session()->pull('state');
        throw_unless(strlen($state) > 0 && $state === $request->state, InvalidArgumentException::class);

        // Đăng nhập và get access_token user thông qua authorization_code
        $response = Http::asForm()->post(env('SERVER_URL').'/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'),
            'redirect_uri' => env('APP_URL').'/callback',
            'code' => $request->code,
        ]);

        // Lưu lại thông tin token sau khi đăng nhập thành công
        $request->session()->put($response->json());
        return redirect('user');
    }

    public function user(Request $request){

        // Get thông tin user thông qua access_token

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '. $this->getAccessToken(),
        ])->get(env('SERVER_URL').'/api/user');

        if(!$response->ok()){
            if($this->getRefreshToken()){
                return $this->refreshtoken();
            }
            return redirect('/');
        }

        $user = $response->object();

        return view('import', compact('user'));
    }

    public function import(ImportRequest $request){
        // Gửi file import qua server

        $import_file = fopen($request->file('import_file'), 'r');
        $filename = 'import_file.' . $request->file('import_file')->getClientOriginalExtension();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '. $this->getAccessToken(),
        ])
        ->attach('import_file', $import_file, $filename)
        ->post(env('SERVER_URL').'/api/import');

        if(!$response->ok()){
            return redirect('/');
        }
        if(isset($response->object()->error)){
            return redirect()->back()->with('error', $response->object()->error);
        }

        return redirect('/user')->with('message', 'Import Success');

    }
}
