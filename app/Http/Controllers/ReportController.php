<?php

namespace App\Http\Controllers;


use App\Models\Client;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ReportCreate\ReportCreate;
use App\Http\Requests\YandexEventReportRequest;
use App\Services\PresentationCreate\PresentationCreate;

class ReportController extends Controller
{
    public function index(YandexEventReportRequest $request, ReportCreate $reportCreate){

        $client = Client::firstWhere('counter_number', $request->counter_number);

        if($report = Report::where('date_start', $request->date1)
                            ->where('date_end', $request->date2)
                            ->where('client_id', $client->id)
                            ->first()
        ){
            

            $path  = "{$report->id}/{$report->client->name}-{$report->client->type}-{$report->date_start}-{$report->date_end}.pptx";
            if(! Storage::disk('report')->exists($path)){
                $presentationClass = new PresentationCreate($report);
                $presentationClass->create(); 
            }
            
           return Storage::disk('report')->download($path);
    
        }else{

            $report = $reportCreate->setClient($client)
                ->setDate1($request->date1)
                ->setDate2($request->date2)
                ->create();
            
            $presentationClass = new PresentationCreate($report);
            $presentationClass->create();

            $path  = "{$report->id}/{$report->client->name}-{$report->client->type}-{$report->date_start}-{$report->date_end}.pptx";

            return Storage::disk('report')->download($path);
        }

    }

    public function getReport(Request $request){
        if($report = Report::find($request->id)){
           
            $path  = "{$report->id}/{$report->client->name}-{$report->client->type}-{$report->date_start}-{$report->date_end}.pptx";
            if(! Storage::disk('report')->exists($path)){
                $presentationClass = new PresentationCreate($report);
                $presentationClass->create(); 
            }
            
           return Storage::disk('report')->download($path);
    
        }
        abort(404);

    }
}
