<?php

namespace App\Exports;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReportExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $params = ['ym:s:vacuumOrganization','ym:s:vacuumSurface','ym:s:vacuumEvent'];

        $resp = Http::withHeaders([
            'Authorization' => 'OAuth y0_AgAAAAA-uo4OAAoCVwAAAADk0hRr4YxzK4hYRjqC8AX1HrQfMnOWYYo',
            'Content-Type' => 'application/x-yametrika+json',
        ])->get('https://api-metrika.yandex.net/stat/v1/data'
        ,
        [
            'ids' => request()->counter_number,
            'date1' => request()->date1,
            'date2' => request()->date2,
            'metrics' => 'ym:s:vacuumevents',
            'dimensions' => implode(',' ,$params),
        ]
    );

        $list = [];
        $addresses = [];
        
        $draftObject = collect();
        //dd($resp);
    
        foreach($resp->json()['data'] as $param){
         
            $string = [];
            for($i = 0; $i < count($params); $i++){

                if($params[$i] == 'ym:s:vacuumOrganization'){
                    $string['address'] = $param['dimensions'][$i]['name'];

                }else if($params[$i] == 'ym:s:vacuumSurface'){
                    $string['service'] = $param['dimensions'][$i]['id'];
                }else if($params[$i] == 'ym:s:vacuumEvent'){
                    $string[$param['dimensions'][$i]['id']] = $param['metrics'][0];
                }
                
            }
           dd($string);

            $status = $draftObject->contains(function ($item, $key) use($string) {
                return ($item['service'] == $string['service'] && $item['address'] == $string['address']);
            });
            

            if($status){
                $draftObject->transform(function ($item, $key) use($string) {
                    if( $item['service'] == $string['service'] && $item['address'] == $string['address'] ){
                        return $item = array_merge($item, $string);
                    }
                    return $item;
                });
            }else{
                $draftObject->push($string);
            }

        }

        $draftObject = $draftObject->sortBy('address');

        $finishObject=[[
            'address' => 'Адрес',
            'service' => 'Сервис',
            'show_org' => 'Просмотров',
            'show-org' => 'Переходов в профиль компании',
            'site' => 'Переходов на сайт',
            'call' => 'Звонков',
            'route' => 'Построенных маршрутов', 
            'cta' => 'Нажатие кнопки действия'
            ]];

        foreach($draftObject as $string){
            $obj=[
                'address' => '',
                'service' => '',
                'show_org' => '',
                'show-org' => '',
                'site' => '',
                'call' => '',
                'route' => '', 
                'cta' => '' 
            ];
            foreach($string as $k => $v){

                if($k == 'service'){
                    $v = Str::replace('.','',$v);
                    $obj[$k] = config("yandex-config.services.$v");
                }else{
                    $obj[$k] = $v; 
                }
                           
            }
            $finishObject[] = $obj;

        }
        return collect($finishObject);

    }
}
