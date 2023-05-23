<?php

namespace App\Service;

use chillerlan\QRCode\QRCode;

class QrCodeGenerator
{
    public function toSrc(string $data):string
    {
        return (new QRCode)->render($data);
    }
}