<?php 

namespace App\Services\ReportCreate\Types;

use DateTime;
use App\Services\ReportCreate\Types\AbstractReportType;
use App\Services\ReportCreate\Contract\ReportCreateInterface;
use App\Services\YandexMetrika\Support\Facades\YandexMetrikaApi;

class ProfileReport extends AbstractReportType implements ReportCreateInterface
{
    
    public const REPORT_QUERY = [
        'current' => [
            'query' => 'getVisitsUsersYandexProfileForPeriodAddressService', 
            'type' => 'current',
            'relation' => 'params',
            'period' => 'Date'
        ],
        'previous' => [
            'query' => 'getVisitsUsersYandexProfileForPeriodAddressService', 
            'type' => 'previous',
            'relation' => 'params',
            'period' => 'PreviousDate'
        ],
        'devices' => [
            'query' => 'getVisitsUsersYandexProfileForPeriodDevice', 
            'type' => null,
            'relation' => 'devices',
            'period' => 'Date'
        ],
        'device_manufactures' => [
            'query' => 'getVisitsUsersYandexProfileForPeriodDeviceManufacture', 
            'type' => null,
            'relation' => 'deviceManufactures',
            'period' => 'Date'
        ],
    ];

    public function getData(): array
    {
        $data = [];
        foreach(self::REPORT_QUERY as $key => $queryParam){
      
            $data[$key] = YandexMetrikaApi::setCounter(
                                config('yandex-metrika-api.token') , 
                                (int)$this->getClient()->counter_number
                            )->{$queryParam['query']}(
                                $this->{'get' . $queryParam['period'] . 1}(), 
                                $this->{'get' . $queryParam['period'] . 2}()
                            );
        }
        return $data;
    }


    public function create()
    {

        $data = $this->getData();

        $reportMetaData=$this->dataConvertaitionToArrayOfObject($data);

        $report = $this->getReportEntity()->create([
            'date_start' => $this->getDate1()->format('Y-m-d'),
            'date_end'   => $this->getDate2()->format('Y-m-d'),
            'client_id'  => $this->getClient()->id
        ]);

        $this->dataMergeWithReport($report, $reportMetaData);

        return $report;


    }

    public function dataMergeWithReport($report, $reportMetaData)
    {
        
        foreach(self::REPORT_QUERY as $key => $queryParam){

            $report->{$queryParam['relation']}()->saveMany($reportMetaData[$key]);
        }
    }

    public function dataConvertaitionToArrayOfObject($data): array
    {
        $reportMetaData=[];
  
        foreach(self::REPORT_QUERY as $key => $queryParam){
            $reportMetaData[$key] = $this->reportMetaParamsTransformToObjects(
                $data[$key], 
                $this->getReportEntity()->{$queryParam['relation']}()->getModel(), 
                $queryParam['type'] 
            );
        
        }
        return $reportMetaData;  
    }

    public function reportMetaParamsTransformToObjects($resp, $class, $type = null): array
    {
        $report_params = [];
        $query_params = $resp->getQuery()->getDimensions();
        $request_data = $resp->getData();

        foreach($request_data as $data){
            $dataDimensions = $data->getDimensions();
        
            $string = [];
            for($i = 0; $i < count($query_params); $i++){

                if($query_params[$i] == 'ym:s:vacuumOrganization'){
                    $string['address'] = $dataDimensions[$i]['name'];
                }else if($query_params[$i] == 'ym:s:vacuumSurface'){
                    $string['service'] = $dataDimensions[$i]['id'];
                }else if($query_params[$i] == 'ym:s:vacuumEvent'){
                    $string['param'] = $dataDimensions[$i]['id'];
                } else if($query_params[$i] == 'ym:s:deviceCategory'){
                    $string['param'] = $dataDimensions[$i]['id'];
                } else if($query_params[$i] == 'ym:s:mobilePhone'){
                    $name = !empty($dataDimensions[$i]['name']) ? $dataDimensions[$i]['name'] : 'undefined';
                    $string['param'] = $name;
                }
            }

            $string['value'] = $data->getMetrics()[0];
            if($type != null){
                $string['type'] = $type;
            }

            $report_params[] = new $class($string);    
        }

        return $report_params;
    }
}