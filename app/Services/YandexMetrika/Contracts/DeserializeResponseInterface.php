<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika\Contracts;

use Psr\Http\Message\ResponseInterface;
use App\Services\YandexMetrika\YandexMetrika;

/**
 * Interface DeserializeResponseInterface
 *
 * @package App\Services\YandexMetrika\Contracts
 */
interface DeserializeResponseInterface
{
    /**
     * Десериализация ответа
     *
     * @param  YandexMetrika  $client
     * @param  ResponseInterface  $response
     * @param  string  $format
     * @return mixed
     */
    public static function deserialize(YandexMetrika $client, ResponseInterface $response, string $format);
}
