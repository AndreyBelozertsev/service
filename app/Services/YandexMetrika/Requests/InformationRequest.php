<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika\Requests;

use App\Services\YandexMetrika\Contracts\Request;
use App\Services\YandexMetrika\Requests\Concerns\RequestCore;

/**
 * Class InformationRequest
 *
 * @package App\Services\YandexMetrika\Requests
 */
class InformationRequest extends RequestCore implements Request
{
    protected const METHOD = 'GET';
    protected const ADDRESS = 'https://api-metrika.yandex.net/management/v1/counter/{counterId}/logrequest/{requestId}';

    /**
     * Идентификатор запроса
     *
     * @var null|int
     */
    private static $requestId = null;

    public function __construct(int $counterId, int $requestId)
    {
        parent::__construct($counterId);

        $this->setRequestId($requestId);
    }

    /**
     * Установка запроса
     *
     * @param  int  $requestId
     * @return InformationRequest
     */
    public function setRequestId(int $requestId): InformationRequest
    {
        self::$requestId = $requestId;

        return $this;
    }

    /**
     * Адрес для отправки запроса
     *
     * @return string
     */
    public function getAddress(): string
    {
        return str_replace('{requestId}', (string)self::$requestId, parent::getAddress());
    }
}
