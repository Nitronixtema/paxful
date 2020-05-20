<?php

namespace App;

use Illuminate\Support\Facades\DB;

class Uuid
{
    public function generate(): string
    {
        return reset(DB::select('SELECT uuid()')[0]);
    }
}
