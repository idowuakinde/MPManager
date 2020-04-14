<?php

namespace App\Observers;

use App\Models\AssetPerson;
use App\Models\AssetRate;

class AssetPersonObserver
{
    /**
     * @var AssetRate
     */
    private $assetRate;

    public function __construct(AssetRate $assetRate)
    {
        $this->assetRate = $assetRate;
    }

    /**
     * Handle the asset person "created" event.
     *
     * @param AssetPerson $assetPerson
     * @return void
     */
    public function created(AssetPerson $assetPerson):void
    {
        $base_time = time();
        foreach (range(1, $assetPerson->rate_count) as $rate) {
            if ((int)$rate === (int)$assetPerson->rate_count) {
                //last rate
                $rate_cost = $assetPerson->total_cost - (($rate - 1) * floor($assetPerson->total_cost / $assetPerson->rate_count));

            } else {
                $rate_cost = floor($assetPerson->total_cost / $assetPerson->rate_count);
            }
            $rate_date = date('Y-m-01', strtotime('+' . $rate . ' month', $base_time));
            $this->assetRate::create([
                'asset_person_id' => $assetPerson->id,
                'rate_cost' => $rate_cost,
                'remaining' => $rate_cost,
                'due_date' => $rate_date
            ]);
        }
    }

    /**
     * Handle the asset person "updated" event.
     *
     * @param AssetPerson $assetPerson
     * @return void
     */
    public function updated(AssetPerson $assetPerson)
    {
        //
    }

    /**
     * Handle the asset person "deleted" event.
     *
     * @param AssetPerson $assetPerson
     * @return void
     */
    public function deleted(AssetPerson $assetPerson)
    {
        //
    }

    /**
     * Handle the asset person "restored" event.
     * @param AssetPerson $assetPerson
     * @return void
     */
    public function restored(AssetPerson $assetPerson)
    {
        //
    }

    /**
     * Handle the asset person "force deleted" event.
     *
     * @param AssetPerson $assetPerson
     * @return void
     */
    public function forceDeleted(AssetPerson $assetPerson)
    {
        //
    }
}