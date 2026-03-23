<?php

namespace App\Services\Przelewy24;

use Przelewy24\Przelewy24;

class Przelewy24Factory
{
    public function make(): Przelewy24
    {
        $posId = config('przelewy24.pos_id');

        return new Przelewy24(
            (int) config('przelewy24.merchant_id'),
            (string) config('przelewy24.reports_key'),
            (string) config('przelewy24.crc'),
            ! (bool) config('przelewy24.sandbox'),
            $posId !== null ? (string) $posId : null,
        );
    }
}
