<?php
namespace App\Facade;

use Illuminate\Support\Facades\Facade;

class ParserService extends Facade
{
    protected static function getFacadeAccessor(){
        return 'Parser';
    }
}