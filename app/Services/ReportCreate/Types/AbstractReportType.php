<?php 
namespace App\Services\ReportCreate\Types;

use DateTime;
use Carbon\Carbon;

abstract class AbstractReportType
{

    protected $date1;

    protected $date2;

    protected $reportEntity;

    protected $client;

    public function setDate1(DateTime $date)
    {
        $this->date1 = $date;
        return $this;
    }

    public function getDate1()
    {
        return $this->date1;
    }

    public function setDate2(DateTime $date)
    {
        $this->date2 = $date;
        return $this;
    }

    public function getDate2()
    {
        return $this->date2;
    }

    public function setReportEntity($entity)
    {
        $this->reportEntity = $entity;
        return $this;
    }

    public function getReportEntity()
    {
        return new $this->reportEntity;
    }

    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function makePreviousDateStart(): DateTime
    {
        return new DateTime(Carbon::createFromFormat('Y-m-d', $this->getDate1()->format('Y-m-d') )
                ->startOfMonth()
                ->subMonth()
                ->toDateString());
    }

    public function makePreviousDateEnd(): DateTime
    {
        $date1 = $this->getDate1()
                ->format('Y-m-d');

        $date2 = $this->getDate2()
                ->format('Y-m-d');

        $dateResp = Carbon::createFromFormat('Y-m-d', $date1)
                ->subMonth();
     
        if( Carbon::createFromFormat('Y-m-d', $date2)->endOfMonth() != 
            Carbon::createFromFormat('Y-m-d', $date2)->endOfDay())
        {
            $dateResp->day = Carbon::createFromFormat('Y-m-d', $date2)->day;
        }else{
            $dateResp->endOfMonth();
        }
        return new DateTime($dateResp->toDateString());
    }

    public function getPreviousDate1()
    {
        return $this->makePreviousDateStart();
    }

    public function getPreviousDate2()
    {
        return $this->makePreviousDateEnd();
    }
}