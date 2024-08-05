<?php

namespace App\Jobs;

use App\Models\ShopedItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPurchaseHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $userId;
    protected $itemName;
    protected $category;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $item_name, $category)
    {
        $this->userId = $user_id;
        $this->itemName = $item_name;
        $this->category = $category;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ShopedItem::query()->create([
            'user_id' => $this->userId,
            'product_name' => $this->itemName,
            'product_type' => $this->category,
            'quantity' => 1,
        ]);
    }
}
