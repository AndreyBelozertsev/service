<?php 

namespace App\Services\ReportCreate;

use DateTime;
use App\Models\Client;
use App\Models\Report;

class ReportCreate

{
    protected $client;

    protected $date1;

    protected $date2;

    protected $reportClass= \App\Models\Report::class;

    public const REPORT_TYPES = [
        'profile' => \App\Services\ReportCreate\Types\ProfileReport::class,
        'site' => \App\Services\ReportCreate\Types\SiteReport::class,
    ];

    

    public function getClient()
    {
        return $this->client;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function setDate1($date): ReportCreate 
    {
        $this->date1 = new DateTime($date);
        return $this;
    }

    public function getDate1()
    {
        return $this->date1;
    }

    public function setDate2($date): ReportCreate 
    {
        $this->date2 = new DateTime($date);
        return $this;
    }

    public function getDate2()
    {
        return $this->date2;
    }

    public function create(){
        return $this->getReportTypeClass()
                ->setDate1($this->getDate1())
                ->setDate2($this->getDate2())
                ->setReportEntity($this->reportClass)
                ->setClient($this->getClient())
                ->create();
    }

    public function getReportTypeClass(){

        $class =  self::REPORT_TYPES[$this->client->type];
        return new $class;
    }

}