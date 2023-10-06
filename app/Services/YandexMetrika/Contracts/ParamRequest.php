<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika\Contracts;

/**
 * Interface ParamRequest
 *
 * @package App\Services\YandexMetrika\Contracts
 */
interface ParamRequest extends Request
{
    /**
     * Параметры запроса
     *
     * @return array
     */
    public function getParams(): array;
}
