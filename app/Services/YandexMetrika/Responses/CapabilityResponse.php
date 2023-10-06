<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika\Responses;

use JMS\Serializer\Annotation as JMS;
use App\Services\YandexMetrika\Responses\Concerns\ErrorResponse;
use App\Services\YandexMetrika\Responses\Types\LogRequestEvaluation;

/**
 * Class CapabilityResponse
 *
 * @package App\Services\YandexMetrika\Responses
 */
class CapabilityResponse
{
    use ErrorResponse;

    /**
     * Оценка возможности создания запросов логов
     * 
     * @JMS\Type("App\Services\YandexMetrika\Responses\Types\LogRequestEvaluation")
     *
     * @var LogRequestEvaluation
     */
    protected $log_request_evaluation;

    /**
     * Оценка возможности создания запросов логов
     *
     * @return LogRequestEvaluation
     */
    public function getLogRequestEvaluation(): LogRequestEvaluation
    {
        return $this->log_request_evaluation;
    }
}
