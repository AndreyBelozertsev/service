<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika\Contracts;

/**
 * Interface Request
 *
 * @package App\Services\YandexMetrika\Contracts
 */
interface Request
{
    /**
     * Адрес для отправки запроса
     *
     * @return string
     */
    public function getAddress(): string;

    /**
     * Метод отправки запроса
     *
     * @return string
     */
    public function getMethod(): string;
}
