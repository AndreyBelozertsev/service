<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\YandexEventReportRequest;



class YandexController extends Controller
{
    public function index(YandexEventReportRequest $request){
        
        //return Excel::download(new ReportExport, "отчет - $request->counter_number.xlsx", \Maatwebsite\Excel\Excel::XLSX);

    }  
}
