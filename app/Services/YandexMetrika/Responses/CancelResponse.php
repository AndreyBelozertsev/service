<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika\Responses;

use JMS\Serializer\Annotation as JMS;
use App\Services\YandexMetrika\Responses\Concerns\ErrorResponse;
use App\Services\YandexMetrika\Responses\Types\LogRequest;

/**
 * Class CancelResponse
 *
 * @package App\Services\YandexMetrika\Responses
 */
class CancelResponse
{
    use ErrorResponse;

    /**
     * Запрос
     * 
     * @JMS\Type("App\Services\YandexMetrika\Responses\Types\LogRequest")
     *
     * @var LogRequest
     */
    protected $log_request;

    /**
     * Запрос
     *
     * @return LogRequest
     */
    public function getLogRequest(): LogRequest
    {
        return $this->log_request;
    }
}
