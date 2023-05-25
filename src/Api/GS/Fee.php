<?php

namespace App\Api\GS;

class Fee
{
    public ?int $blocks = null;
    public ?string $btc_kvb = null;
    public ?string $sat_vb = null;

    /**
     * 1 BTC/kvB = 100000 sat/vB
     * @return void 
     */
    public function calculateSatVb():void
    {
        $this->sat_vb = bcmul($this->btc_kvb, 100000, 4);
    }
}