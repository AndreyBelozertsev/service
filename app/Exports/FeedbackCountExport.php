<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class FeedbackCountExport implements FromArray
{
    protected $list;

    public function __construct(array $list)
    {
        $this->list = $list;
    }

    public function array(): array
    {
        return $this->list;
    }
}