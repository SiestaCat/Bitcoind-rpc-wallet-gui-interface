<?php

namespace App\Api\GS;

class Wallet
{
    public string $name;
    public bool $is_loaded = false;
    public string $balance_available = '0';
    public string $balance_pending = '0';
    public array $addresses = [];
}