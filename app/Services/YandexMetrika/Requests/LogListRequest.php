<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika\Requests;

use App\Services\YandexMetrika\Contracts\Request;
use App\Services\YandexMetrika\Requests\Concerns\RequestCore;

/**
 * Class LogListRequest
 *
 * @package App\Services\YandexMetrika\Requests
 */
class LogListRequest extends RequestCore implements Request
{
    protected const METHOD = 'GET';
    protected const ADDRESS = 'https://api-metrica.yandex.net/management/v1/counter/{counterId}/logrequests';
}
