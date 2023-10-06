<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika\Responses;

use Psr\Http\Message\ResponseInterface;
use App\Services\YandexMetrika\Contracts\DeserializeResponseInterface;
use App\Services\YandexMetrika\Responses\Concerns\ErrorResponse;
use App\Services\YandexMetrika\YandexMetrika;

/**
 * Class DownloadResponse
 *
 * @package App\Services\YandexMetrika\Responses
 */
class DownloadResponse implements DeserializeResponseInterface
{
    use ErrorResponse;

    /**
     * Десериализация ответа
     *
     * @param  YandexMetrika  $client
     * @param  ResponseInterface  $response
     * @param  string  $format
     * @return array|mixed|object|\Psr\Http\Message\StreamInterface
     */
    public static function deserialize(YandexMetrika $client, ResponseInterface $response, string $format)
    {
        if (200 === $response->getStatusCode()) {
            return $response->getBody();
        }

        return $client->getSerializer()->deserialize($response->getBody()->getContents(), self::class, $format);
    }
}
