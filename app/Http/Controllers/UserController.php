<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Inventory;
use App\Models\Users;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 检查用户是否存在
     * @param Request $request
     * @return mixed
     */
//    public function existOrNot(Request $request)
//    {
//        $userId = $request->input('user_id');
//        $count = Users::query()
//            ->where('user_id', $userId)
//            ->count();
//        return $this->success([
//            'exist' => $count
//        ]);
//    }

    public function existOrNot(Request $request)
    {
        $userId = $request->input('user_id');
        $user = Users::where('user_id', $userId)->first();

        if ($user) {
            return response()->json([
                'code' => 200,
                'msg' => 'User exists',
                'data' => ['exist' => 1]
            ]);
        } else {
            return response()->json([
                'code' => 404,
                'msg' => 'user not found',
                'data' => ['exist' => 0]
            ]);
        }
    }


    public function store(Request $request)
    {
        $userId = $request->input('user_id');
        $userName = $request->input('user_name');
        if(empty($userId)){
            return $this->failed('parameter error');
        }
        $count = Users::query()
            ->where('user_id', $userId)
            ->count();
        if($count > 0){
            return $this->failed('already exist');
        }

        Users::query()
            ->create(
                ['user_id' => $userId,
                'user_name' => $userName,
                'coins' => 0,
                'diamonds' => 0,
                'level' => 1,
                'current_experience' => 0,
                'experience_for_next_level' => 1,
                'rod_type' => 'Plastic Rod',
                'fish_inventory' => '']
            );
        return $this->success();
    }
    public function basic(Request $request){

        $userId = $request->input('user_id');
        $userName = Users::query()
            ->where('user_id', $userId)
            ->select('user_name', 'rod_type')
            ->first();

        return $this->success($userName);
    }

    public function finance(Request $request){

        $userId = $request->input('user_id');
        $info = Users::query()
            ->where('user_id', $userId)
            ->select('coins', 'diamonds')
            ->first();

        return $this->success($info);
    }

    public function level(Request $request){

        $userId = $request->input('user_id');
        $info = Users::query()
            ->where('user_id', $userId)
            ->select('level','current_experience','experience_for_next_level')
            ->first();
        return $this->success($info);
    }

    public function inventory(Request $request){

        $userId = $request->input('user_id');
        $info = Inventory::query()
            ->where('user_id', $userId)
            ->select('url','type','weight','description','price')
            ->first();
        return $this->success($info);
    }

    public function achievement(Request $request){

        $userId = $request->input('user_id');
        $info = Achievement::query()
            ->where('user_id', $userId)
            ->select('type','weight')
            ->first();
        return $this->success($info);
    }

}
