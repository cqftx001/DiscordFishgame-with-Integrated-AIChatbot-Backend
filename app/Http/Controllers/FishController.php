<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Users;
use Illuminate\Http\Request;
use App\Models\FishImages;
use App\Models\Fish;
use App\Http\Controllers\ChatGPTController;

class FishController extends Controller
{
    public function catch(Request $request)
    {
        $userId = $request->input('user_id');
        $fishList = Fish::query()
            ->where('status', '1')
            ->get()
            ->toArray();

        $chatGPTController = new ChatGPTController();
        $fishChosen = $this->probabilityHelper($fishList);
        $weight = $this->weightGenerator($fishChosen);

        $prompt = $fishChosen['type'] . " " . $fishChosen['description'];
        $drawRequest = new Request(['prompt' => $prompt]);
        $imgResponse = $chatGPTController->draw($drawRequest);
        $img = json_decode($imgResponse->getContent());

        if (isset($img->data)) {
            $imgUrl = $img->data;
        } else {
            $imgUrl = 'https://s3.us-west-1.amazonaws.com/fishing.web.images/Fishing+Game+Images/Other/pearl.png';
        }

        Inventory::query()
            ->create([
                'user_id' => $userId,
                'type' => $fishChosen['type'],
                'price' => 1,
                'weight' => $weight,
                'url' => $imgUrl,
                'description' => $fishChosen['description']
            ]);

        return response()->json([
            'type' => $fishChosen['type'],
            'weight' => $weight,
            'description' => $fishChosen['description'],
            'url' => $imgUrl,
            'status' => 'Successfully Caught!'
        ]);
    }


    public function sell(Request $request)
    {
        $userId = $request->input('userid');
        $inventoryItems = Inventory::query()
            ->where('user_id', $userId)
            ->get();

        $totalPrice = $inventoryItems->sum(function ($item) {
            return $item->weight * 1;
        });

        Inventory::query()
            ->where('user_id', $userId)
            ->delete();

        return response()->json(['status' => 'Sell successfully!', 'totalPrice' => $totalPrice]);
    }


    private function probabilityHelper($fishList){
        $totalProbability = array_sum(array_map(function($fish){
            return $fish['probability'];
        }, $fishList));
        $randomProbability = mt_rand() / mt_getrandmax() * $totalProbability;
        $currentProb = 0;
        foreach($fishList as $fish){
            $currentProb += $fish['probability'];
            if($currentProb > $randomProbability){
                return $fish;
            }
        }
        return end($fish);
    }

    private function weightGenerator($fish): float
    {
//            if (!isset($fish['mean']) || !isset($fish['standard_deviation'])) {
//                throw new \Exception('Mean is not defined for the selected fish');
//            }
//            else if(!isset($fish['standard_deviation'])){
//                throw new \Exception('Stance is not defined for the selected fish');
//            }
//            $mean = $fish['mean'];
//            $standardDeviation = $fish['standard_deviation'];
        $mean = 1.75;
        $standardDeviation = 0.625;
        $u1 = mt_rand() / mt_getrandmax();
        $u2 = mt_rand() / mt_getrandmax();
        $randStdNormal = sqrt(-2 * log($u1)) * sin(2 * pi() * $u2);
        return $mean + $standardDeviation * $randStdNormal;
    }

    private function imageHelper($weight, $fishChosen)
    {

        $score = $this->evaluate($weight, $fishChosen);
        $fishImg = FishImages::query()
            ->where('type', $fishChosen['type'])
            ->first(['images']);

        // 确保 $fishImg 不是 null 并且 $fishImg->images 存在
        if ($fishImg && isset($fishImg->images)) {
            $images = $fishImg->images;

            // 确保 $images[$score] 和 $images[$score][0]['url'] 存在
            if (isset($images[$score][0]['url'])) {
                return $images[$score][0]['url'];
            }
        }

        // 如果上述检查失败，返回一个默认的 URL 或错误处理
        return 'https://s3.us-west-1.amazonaws.com/fishing.web.images/Fishing+Game+Images/Other/pearl.png';
        //        $score = $this->evaluate($weight, $fishChosen);
//        print($score);
//        $fishImg = FishImages::query()
//            ->where('type', $fishChosen['type'])
//            ->first(['images']);
//        $images = $fishImg ? $fishImg->images : null;
//        return $images[$score][0]['url'];
    }


    private function evaluate($weight, $fishChosen): string
    {
        $sWeight = $fishChosen['s_weight'] ?? 20;
        $aWeight = $fishChosen['a_weight'] ?? 19;
        $bWeight = $fishChosen['b_weight'] ?? 18;
        $cWeight = $fishChosen['c_weight'] ?? 17;

        if ($weight > $sWeight) {
            return 'SS';
        } else if ($weight > $aWeight) {
            return 'S';
        } else if ($weight > $bWeight) {
            return 'A';
        } else if ($weight > $cWeight) {
            return 'B';
        } else {
            return 'C';
        }
    }

//    private function evaluate($weight, $fishChosen)
//{
//    if ($weight > $fishChosen['s_weight']) {
//        return 'SS';
//    } else if ($weight > $fishChosen['a_weight']) {
//        return 'S';
//    } else if ($weight > $fishChosen['b_weight']) {
//        return 'A';
//    } else if ($weight > $fishChosen['c_weight']) {
//        return 'B';
//    } else {
//        return 'C';
//    }
//}

}
