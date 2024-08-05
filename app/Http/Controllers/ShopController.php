<?php

namespace App\Http\Controllers;
use App\Jobs\ProcessPurchaseHistory;
use App\Models\ShopedItem;
use App\Models\ShopItem;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ShopController extends Controller
{

    public function listItems(){
        $cacheKey = 'shop_items';

        if (Redis::exists($cacheKey)) {
            $items = json_decode(Redis::get($cacheKey), true);
        } else {
            $items = ShopItem::all();
            Redis::set($cacheKey, json_encode($items), 'EX', 600); // 缓存10分钟
        }
        return $this->success('Items retrieved successfully', $items);
    }

    public function purchaseItems(Request $request){
        $userId = $request -> input('user_id');
        $itemName = $request->input('item_name');
        $category = $request->input('category');
        $lockKey = 'shop_items_lock_' . $userId . '_' . $itemName;
        $lock = Redis::set($lockKey, 1, 'NX', 'EX', 10);


        if(!$lock){
            return $this->failed("Too many request! Try again later!");
        }
        try {
            $user = Users::query()
                ->where('user_id', $userId)
                ->first();


            if (!$user) {
                return $this->failed("User not found");
            }

            $item = ShopItem::query()
                ->where('category', $category)
                ->where('name', $itemName)
                ->first();

            if (!$item) {
                return $this->failed("Item not found");
            }

            $currency = $this->determineCurrency($category);
            $price = $item->{$currency};

            if ($user->{$currency} < $price) {
                return $this->failed("User does not have enough {$currency}");
            }

            $user->{$currency} -= $price;
            $user->save();

            $ifExists = ShopedItem::query()
                ->where('user_id', $userId)
                ->where('product_type', $category)
                ->where('product_name', $itemName)
                ->exists();

            if ($ifExists) {
                $item->increment('quantity');
            } else {
                ProcessPurchaseHistory::dispatch($userId, $itemName, $category);
            }

            return $this->success("Successfully purchased item!");
        }finally {
            // 释放锁
            Redis::del($lockKey);
        }
    }

    private function determineCurrency($category){
        $diamondCategories = ['Time Acceleration Card', 'Food'];
        return in_array($category, $diamondCategories) ? 'diamonds' : 'coins';
    }
}
