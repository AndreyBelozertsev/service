<?php

namespace App\Services\YandexMetrika\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static App\Services\YandexMetrika\YandexMetrika setCounter(string $token, int $counterId, int $cacheLifetime)
 * @method static App\Services\YandexMetrika\YandexMetrika setToken(string $token)
 * @method static App\Services\YandexMetrika\YandexMetrika setCounterId(int $counterId)
 * @method static App\Services\YandexMetrika\YandexMetrika setCacheLifetime(int $cacheLifetime)
 * @method static App\Services\YandexMetrika\YandexMetrika setHttpClient(\GuzzleHttp\Client $httpClient)
 *
 * @method static App\Services\YandexMetrika\Responses\CapabilityResponse getCapabilityResponse(string $startDate, string $endDate, string $source, array $fields)
 * @method static App\Services\YandexMetrika\Responses\CreateResponse getCreateResponse(string $startDate, string $endDate, string $source, array $fields)
 * @method static App\Services\YandexMetrika\Responses\CancelResponse getCancelResponse(int $requestId)
 * @method static App\Services\YandexMetrika\Responses\InformationResponse getInformationResponse(int $requestId)
 * @method static App\Services\YandexMetrika\Responses\DownloadResponse getDownloadResponse(int $requestId, int $partNumber)
 * @method static App\Services\YandexMetrika\Responses\CleanResponse getCleanResponse(int $requestId)
 * @method static App\Services\YandexMetrika\Responses\LogListResponse getLogListResponse()
 *
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getMetrikaResponse(array $params)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getVisitsViewsUsers(int $days)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getVisitsViewsUsersForPeriod(DateTime $startDate, DateTime $endDate)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getTopPageViews(int $days, int $limit)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getTopPageViewsForPeriod(DateTime $startDate, DateTime $endDate, int $limit)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getSourcesSummary(int $days)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getSourcesSummaryForPeriod(DateTime $startDate, DateTime $endDate)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getSourcesSearchPhrases(int $days, int $limit)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getTechPlatforms(int $days, int $limit)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getTechPlatformsForPeriod(DateTime $startDate, DateTime $endDate, int $limit)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getVisitsUsersSearchEngine(int $days, int $limit)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getVisitsUsersSearchEngineForPeriod(DateTime $startDate, DateTime $endDate, int $limit)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getVisitsViewsPageDepth(int $days, int $pages)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getVisitsViewsPageDepthForPeriod(DateTime $startDate, DateTime $endDate, int $pages)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getGeoCountry(int $days, int $limit)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getGeoCountryForPeriod(DateTime $startDate, DateTime $endDate, int $limit)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getGeoArea(int $days, int $limit, int $countryId)
 * @method static App\Services\YandexMetrika\Responses\MetrikaResponse getGeoAreaForPeriod(DateTime $startDate, DateTime $endDate, int $limit, int $countryId)
 *
 * @see App\Services\YandexMetrika\YandexMetrika
 */
class YandexMetrikaApi extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'yandexMetrikaApi';
    }
}
