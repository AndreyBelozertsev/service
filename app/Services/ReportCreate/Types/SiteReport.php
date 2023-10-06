<?php 

namespace App\Services\ReportCreate\Types;

use App\Services\ReportCreate\Types\AbstractReportType;
use App\Services\ReportCreate\Contract\ReportCreateInterface;
use App\Services\YandexMetrika\Support\Facades\YandexMetrikaApi;

class SiteReport extends AbstractReportType implements ReportCreateInterface
{
    public const REPORT_QUERY = [

        'current' => [
            'type' => 'current',
            'query' => 'getVisitsUsersSourceGoalForPeriod', 
            'relation' => 'goals',
            'period' => 'Date',
            'basic' => true,
        ],
        'previous' => [
            'type' => 'previous',
            'query' => 'getVisitsUsersSourceGoalForPeriod', 
            'relation' => 'goals',
            'period' => 'PreviousDate',
            'basic' => true,
        ],

        'goalDevices' => [
            'type' => null,
            'query' => 'getReachGoalDevicesForPeriod', 
            'relation' => 'goalDevices',
            'period' => 'Date',
            'basic' => false,
        ],
    ];

    public const METRIC_BASIC_GOALS = [
        'ym:s:visits' => 'Визиты', 
        'ym:s:users' => 'Посетители'
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
                                $this->{'get' . $queryParam['period'] . 2}(),
                                $this->getGoals($queryParam['basic'])
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
        $query_params = $resp->getQuery()->getMetrics();
        $request_data = $resp->getData();
        $paramsEncrypt = $this->getGoalsEncrypt();
        
        foreach($request_data as $data){
            $dataDimensions = $data->getDimensions()[0];
            $values = $data->getMetrics();
            $string = [];
   
            for($i = 0; $i < count($query_params); $i++){
                $string['source'] = $dataDimensions['id'];
                $string['param'] = $paramsEncrypt[$query_params[$i]];
                $string['value'] = $values[$i];
                
                if($type != null){
                    
                    $string['type'] = $type;
                }
          
                $report_params[] = new $class($string); 
            }
            
        }
        return $report_params;
    }
    

    public function getGoals($withBasic = true): array
    {
        $goals=[];
        if($withBasic == true){
            foreach(self::METRIC_BASIC_GOALS as $key => $value){
                $goals[] = $key;
            }
        }

        foreach($this->client->goals as $goal){
            $key = 'ym:s:goal' . $goal->goal_id  .'reaches';
            $goals[] = $key;
        }

        return $goals;
    }

    public function getGoalsEncrypt($withBasic = true): array
    {
        $goalsEncrypt = [];

        if($withBasic == true){
            $goalsEncrypt=self::METRIC_BASIC_GOALS;
        }
        foreach($this->client->goals as $goal){
            $key = 'ym:s:goal' . $goal->goal_id  .'reaches';
            $goalsEncrypt[$key] = $goal->name;
        }
        return $goalsEncrypt;
    }
 
}