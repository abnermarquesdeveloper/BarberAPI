<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Barber;
use App\Models\UserFavorite;

class UserController extends Controller
{
    private $loggedUser;

    public function __construct(){
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function read(){
        $array = ['error' => ''];

        $info = $this->loggedUser;
        $array['avatar'] = url('media/avatars/'.$info['avatar']);
        $array['data'] = $info;


        return $array;
    }

    public function toggleFavorite(Request $request){
        $array = ['error'=>''];

        $id_barber = $request->input('barber');

        $barber = Barber::find($id_barber);

        if($barber){
            $fav = UserFavorite::select()
                ->where('id_user', $this->loggedUser->id)
                ->where('id_barber', $id_barber)
                ->first();
            if($fav){
                $fav->delete();
                $array['have'] = false;
            }else{
                $newFav = new UserFavorite();
                $newFav->id_user = $this->loggedUser->id;
                $newFav->id_barber = $id_barber;
                $newFav->save();
                $array['have'] = true;
            }
        }else{
            $array['error'] = 'Barbeiro inexistente!';
        }

        return $array;
    }
}
