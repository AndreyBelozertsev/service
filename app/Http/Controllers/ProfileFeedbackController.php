<?php

namespace App\Http\Controllers;

use AdminSection;
use Illuminate\Http\Request;

use App\Exports\FeedbackCountExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\GetReportsCountRequest;
use App\Services\FeedbackControlService\FeedbackControlService;

class ProfileFeedbackController extends Controller
{
    public function index()
    {
        return AdminSection::view(view('admin.get_feedback'), 'Получить количество отзывов за период');
    }

    public function getFeedbacksCount(GetReportsCountRequest $request){
    
        $service = new FeedbackControlService();
        $export = new FeedbackCountExport( $service->getCountForPeriod($request->date1, $request->date2) );
        return Excel::download($export ,'count.xlsx');
    }
    
}


