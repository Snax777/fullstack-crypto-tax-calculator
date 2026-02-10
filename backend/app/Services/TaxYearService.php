<?php

namespace App\Services;

use Carbon\Carbon;

class TaxYearService
{
    public function getTaxYear(Carbon $date): int
    {
        if ($date->month < 3) {
            return $date->year - 1;
        }

        return $date->year;
    }
}
