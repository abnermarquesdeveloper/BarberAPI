<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{

    public function __construct(){
        $this->middleware('auth:api', ['except' => ['create','login']]);
    }

    public function create(Request $request){
        $array = ['error'=> ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if(!$validator->fails()){
           
            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');

            $emailExist = User::where('email', $email)->count();
            if($emailExist === 0){
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $newUser = new User();
                $newUser->name = $name;
                $newUser->email = $email;
                $newUser->password = $hash;
                $newUser->save();

                $token = auth()->attempt([
                    'email' => $email,
                    'password' => $password
                ]);
                if(!$token){
                    $array['error'] = 'Ocorreu erro no login!';
                    return $array;
                }
                
                $infoUser = auth()->user();
                $infoUser['avatar'] = url('media/avatars/'.$infoUser['avatar']);
                $array['data'] = $infoUser;
                $array['token'] = $token;

            }else{
                $array['error'] = 'E-mail já cadastrado!';
            }

        }else{
            $array['error'] = 'Dados incorretos!';
            return $array;
        }

        return $array;
    }

    public function login(Request $request){
        $array = ['error' => ''];

        $email = $request->input('email');
        $password = $request->input('password');

        $token = auth()->attempt([
            'email' => $email,
            'password' => $password
        ]);

        if(!$token){
            $array['error'] = 'Email e/ou senha inválidos!';
            return $array;
        }

        $infoUser = auth()->user();
        $infoUser['avatar'] = url('media/avatars/'.$infoUser['avatar']);
        $array['data'] = $infoUser;
        $array['token'] = $token;

        return $array;
    }

    public function logout(){
        auth()->logout();
        return ['error' => ''];
    }

    public function refresh(){
        $array = ['error' => ''];

        $token = auth()->refresh();

        $infoUser = auth()->user();
        $infoUser['avatar'] = url('media/avatars/'.$infoUser['avatar']);
        $array['data'] = $infoUser;
        $array['token'] = $token;

        return $array;
    }
}
