<?php

namespace App\Services\PresentationCreate;

use Carbon\Carbon;

use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Shape\Chart\Series;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Bar;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Pie;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Line;
use \PhpOffice\PhpPresentation\Shape\Chart\Gridlines;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Bar3D;
use PhpOffice\PhpPresentation\Slide\Background\Image;

class PresentationCreate

{
    public const PRESENTATION_TYPES = [
        'profile' => \App\Services\PresentationCreate\Types\ProfilePresentation::class,
        'site' => \App\Services\PresentationCreate\Types\SitePresentation::class,
    ];

    public $report;

    public $addresses;

    public $client;

    public function __construct($report){
        $this->client = $report->client;
        $this->report = $report;
        $this->addresses = $this->report->params()
                        ->select('address')
                        ->where('report_id', $this->report->id)
                        ->distinct('address')
                        ->get()->toArray();

    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function getPresentationTypeClass(){

        $class =  self::PRESENTATION_TYPES[$this->client->type];
        return new $class($this->report);
    }

    public function create(){
        return $this->getPresentationTypeClass()
                    ->create();
    }


    public function createOld(){

        $objPHPPowerPoint = new PhpPresentation();
        $objPHPPowerPoint->removeSlideByIndex(0);
    
        $this->slide1($objPHPPowerPoint);

        foreach($this->addresses as $address){
            $this->slide2($objPHPPowerPoint, $address['address']);
        }

        foreach($this->addresses as $address){
            $this->slide3($objPHPPowerPoint, $address['address']);
        }

        foreach($this->addresses as $address){
            $this->slide4($objPHPPowerPoint, $address['address']);
        }

        foreach($this->addresses as $address){
            $this->slide5($objPHPPowerPoint, $address['address']);
        }

        foreach($this->addresses as $address){
            $this->slide6($objPHPPowerPoint, $address['address']);
        }

        $this->slide7($objPHPPowerPoint);

        $objPHPPowerPoint->getLayout()->setDocumentLayout('A4');
     
        $oWriterPPTX = IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
        $name = "{$report->client->name}-{$report->client->type}-{$report->date_start}-{$report->date_end}.pptx";

        if(!Storage::disk('report')->exists('/'. $this->report->id)) {
            Storage::disk('report')->makeDirectory('/'. $this->report->id, 0775, true); //creates directory
        }
   
        $oWriterPPTX->save(Storage::disk('report')->path($this->report->id . "/$name"));
        return $name;
    
    }

    public function slide1($objPHPPowerPoint){
        $currentSlide = $objPHPPowerPoint->createSlide();

        $oBkgImage = new Image();
        $oBkgImage->setPath(\storage_path(). '/app/report-slide-bg.png');
        $currentSlide->setBackground($oBkgImage);
      // Create a shape (drawing)
        //ширина 1122.51px
        //Create a shape (text)
        $shape = $currentSlide->createRichTextShape()
                ->setHeight(300)
                ->setWidth(600)
                ->setOffsetX(261.25)
                ->setOffsetY(200);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
        $textRun = $shape->createTextRun($this->report->client->name);
        $textRun->setLanguage('ru-RU')->getFont()->setName('Dela Gothic One')->setBold(false)
                        ->setSize(60)
                        ->setColor( new Color( '000' ) );

        $shape = $currentSlide->createRichTextShape()
                ->setHeight(100)
                ->setWidth(600)
                ->setOffsetX(261.25)
                ->setOffsetY(550);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
        $textRun = $shape->createTextRun($this->current_period());
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(24)
                        ->setColor( new Color( '000' ) );
        
                        $shape = $currentSlide->createRichTextShape()
                        ->setHeight(100)
                        ->setWidth(600)
                        ->setOffsetX(261.25)
                        ->setOffsetY(600);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
        $textRun = $shape->createTextRun('Подготовлено компанией "Ракурс"');
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(18)
                        ->setColor( new Color( 'FF000000' ) );
        
    }

    public function slide2($objPHPPowerPoint, $address){
        $currentSlide = $objPHPPowerPoint->createSlide();
        $dataRaw = $this->report->params()->where('report_id', $this->report->id)
                                ->where('address', $address)
                                ->whereIn('param',['show-org','show_org'])
                                ->get()->groupBy('type');

        $data = [];
        $total = [];

        foreach($dataRaw as $key => $param){
            $key = Str::replace('.', '', $key);
            $key = config("yandex-config.periods.$key");
            $total[$key]=0;
           foreach($param as $string){
                
                $service = Str::replace('.', '', $string['service']);
                $servTranslate = config("yandex-config.services.$service");
                if(! isset($data[$key][$servTranslate])){
                    $data[$key][$servTranslate] = 0;
                }
                $data[$key][$servTranslate] += $string->value;
                $total[$key] += $string->value;
            }
            $data[$key] = collect($data[$key])->sortKeys()->toArray();
        }
        $data = collect($data)->sortKeys()->toArray();
        
        $word = '';
        $percent = 0;

        if(isset($total['Предыдущий период'])){
            $percent = (1 - ($total['Текущий период'] / $total['Предыдущий период'])) *100;
            $percent = round($percent, 2);
            if(($total['Текущий период'] - $total['Предыдущий период']) > 0){
                $word = " увеличилось на $percent %";
            }elseif( ($total['Текущий период'] - $total['Предыдущий период']) < 0 ){
                $word = " уменьшилось на $percent %";
            }else{
                $word = " не изменилось";
            }
        }

        $this->create_slide_template($currentSlide);

        $this->create_slide_title($currentSlide, 'Переходы в профиль из сервисов Яндекс');

        $this->create_address_string($currentSlide, $address);

        $shape = $currentSlide->createRichTextShape()
                    ->setHeight(500)
                    ->setWidth(400)
                    ->setOffsetX(650)
                    ->setOffsetY(190);
        $shape->getActiveParagraph()->setSpacingAfter(16)->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $textRun = $shape->createTextRun('График показывает сколько раз пользователи Яндекс просматривали профиль организации, в пред просмотре и в каком сервисе.');
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(16)
                    ->setColor( new Color( '000' ) );
                    
                    
        $shape->createParagraph()->setSpacingAfter(16);
        $textRun = $shape->createTextRun('Количество переходов в профиля за ' . $this->current_period() . $word);
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(16)
                    ->setColor( new Color( '000' ) );
        
        
        $oLine = new Line();
        $oGridLines = new Gridlines();
        $oGridLines->getOutline()->setWidth(10);
        $oGridLines->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color(Color::COLOR_BLUE));
                    
        $oShape = $currentSlide->createChartShape()
                    ->setHeight(500)
                    ->setWidth(500)
                    ->setOffsetX(101)
                    ->setOffsetY(190);
        $oShape->getPlotArea()->getAxisX()->setMajorGridlines($oGridLines);
        $oShape->getTitle()->setVisible(false);
        $oShape->getLegend()->setPosition('b');

        $oBarChart  =  new Bar();
        $i=0;
        foreach($data as $key => $param){
            $color = new Color(config("yandex-config.report_colors.$i"));
            $series  =  new Series($key, $param); 
            $series->setLabelPosition(Series::LABEL_OUTSIDEEND);
            $series->getFill()->setFillType('solid')->setStartColor($color);
            $oBarChart->addSeries( $series ); 
            $i++;
        }
        
        $oBarChart->setBarGrouping( Bar::GROUPING_CLUSTERED ); 
        $oBarChart->setOverlapWidthPercent(-25);

        $oShape->getPlotArea()->getAxisX()->setTitle('');
        $oShape->getPlotArea()->getAxisY()->setTitle('');
        $oShape->getPlotArea()->setType($oBarChart);
            

    }


    public function slide3($objPHPPowerPoint, $address){
        $currentSlide = $objPHPPowerPoint->createSlide();


        $dataRaw = $this->report->params()->where('report_id', $this->report->id)
                                ->where('address', $address)
                                ->whereIn('param',['site','route','call'])
                                ->get()->groupBy('param');
        
 
        $data=[];
        $total = '';
        $totalValue = 0;
        foreach($dataRaw as $key => $param){
            $dataRaw = [
                'Предыдущий период' => 0,
                'Текущий период' => 0,
            ];
     
            foreach($param as $string){
                $dataRaw[config('yandex-config.periods.' . $string['type'])] += $string['value'];
               
                if(config('yandex-config.periods.' . $string['type']) == 'Текущий период' && $string['value'] > $totalValue){
                    $totalValue = $string['value'];
                    $total = $key; 
                }
            }
            $data[$key] = $dataRaw;
        }

        $this->create_slide_template($currentSlide);

        $this->create_slide_title($currentSlide, 'Действия в профиле');

        $this->create_address_string($currentSlide, $address);


        $shape = $currentSlide->createRichTextShape()
                    ->setHeight(500)
                    ->setWidth(400)
                    ->setOffsetX(650)
                    ->setOffsetY(190);
        $shape->getActiveParagraph()->setSpacingAfter(16)->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $textRun = $shape->createTextRun('На этом графике можно увидеть, какие действия пользователи совершают, перейдя в профиль вашей организации.');
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(16)
                    ->setColor( new Color( '000' ) );

        $shape->createParagraph()->setSpacingAfter(16);
        $textRun = $shape->createTextRun('Наиболее популярное действие, за '. $this->current_period() . ' является - ' . config("yandex-config.params_translate.$total"));
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(16)
                    ->setColor( new Color( '000' ) );
        
        
        $oLine = new Line();
        $oGridLines = new Gridlines();
        $oGridLines->getOutline()->setWidth(10);
        $oGridLines->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color(Color::COLOR_BLUE));
                    
        $oShape = $currentSlide->createChartShape()
                    ->setHeight(500)
                    ->setWidth(500)
                    ->setOffsetX(101)
                    ->setOffsetY(190);
        $oShape->getPlotArea()->getAxisX()->setMajorGridlines($oGridLines);
        $oShape->getTitle()->setVisible(false);
        $oShape->getLegend()->setPosition('b');

        $oBarChart  =  new Bar();
        $i=0;
        foreach($data as $key => $param){
            $color = new Color(config("yandex-config.report_colors.$i"));
            $series  =  new Series(config("yandex-config.params_translate.$key"), $param); 
            $series->setLabelPosition(Series::LABEL_OUTSIDEEND);
            $series->getFill()->setFillType('solid')->setStartColor($color);
            $oBarChart->addSeries( $series ); 
            $i++;
        }


        $oBarChart->setBarGrouping( Bar::GROUPING_CLUSTERED ); 
        $oBarChart->setOverlapWidthPercent(-25);

        $oShape->getPlotArea()->getAxisX()->setTitle('');
        $oShape->getPlotArea()->getAxisY()->setTitle('');
        $oShape->getPlotArea()->setType($oBarChart);
    
    }

    public function slide4($objPHPPowerPoint, $address){

        $dataRaw = $this->report->params()->where('report_id', $this->report->id)
                                ->where('type','current')
                                ->where('address', $address)
                                ->whereIn('param',['site','route','call'])
                                ->get()->groupBy('param');

        $data=[];
        $total=[];
        foreach($dataRaw as $key => $param){
            $total[$key]['summary'] = 0;
            $groupByService = collect($param)->groupBy('service');
            foreach($groupByService as $service => $group){
                foreach($group as $string){
                    $service = Str::replace('.', '', $service);
                    $servTranslate = config("yandex-config.services.$service");
                    $data[$key][$servTranslate] = (isset($data[$key][$servTranslate]) ? $data[$key][$servTranslate]: 0) + $string->value;
                    $total[$key]['summary'] += $string->value;
                }
            }
        }
    
        foreach($data as $key => $params){
            $total[$key]['service'] = '';
            $total[$key]['value'] = 0;
            foreach($params as $service => $string){
                if($string > $total[$key]['value']){
                    $total[$key]['value'] = $string;
                    $total[$key]['service'] = $service;
                }
            }
        }
  
        $currentSlide = $objPHPPowerPoint->createSlide();

        $this->create_slide_template($currentSlide);

        $this->create_slide_title($currentSlide, 'CR профиля');

        $this->create_address_string($currentSlide, $address);

        $shape = $currentSlide->createRichTextShape()
                    ->setHeight(500)
                    ->setWidth(500)
                    ->setOffsetX(600)
                    ->setOffsetY(190);
        $shape->getActiveParagraph()->setSpacingAfter(16)->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $textRun = $shape->createTextRun('На этом графике можно увидеть, какие действия пользователи совершают, перейдя в профиль вашей организации.');
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(14)
                    ->setColor( new Color( '000' ) );

        foreach($total as $key => $string){
            $percent = round($string['value']/$string['summary']*100, 0);
            $key = config("yandex-config.params_translate.$key");
            $shape->createParagraph()->setSpacingAfter(12);
            $textRun = $shape->createTextRun( "$percent% от количества целевого действия \"$key\" происходят в сервисе " . $string['service'] );
            $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(14)
                        ->setColor( new Color( '000' ) );
            $shape->createParagraph()->setSpacingAfter(24);
            $textRun = $shape->createTextRun("__% визитов,  с сервиса " . $string['service'] . " содержат, достижение цели \"$key\"");
            $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(14)
                        ->setColor( new Color( '000' ) );
        }

                
        $i=0;
        foreach($data as $key => $action){

            $oLine = new Line();
            $oGridLines = new Gridlines();
            $oGridLines->getOutline()->setWidth(10);
            $oGridLines->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(new Color(Color::COLOR_BLUE));
                        
            $oShape = $currentSlide->createChartShape()
                        ->setHeight(170)
                        ->setWidth(480)
                        ->setOffsetX(101)
                        ->setOffsetY(190  + ($i * 200));
            $oShape->getPlotArea()->getAxisX()->setMajorGridlines($oGridLines);
            $oShape->getLegend()->setOffsetX(150);
            $oShape->getTitle()->setWidth(500)->setText(config("yandex-config.params_translate.$key"));
            $oShape->getTitle()->getFont()->setSize(14);
            
            
            $oSeries  =  new Series(config("yandex-config.params_translate.$key"), $action); 
            $oSeries->setLabelPosition(Series::LABEL_OUTSIDEEND);
            $oSeries->setShowPercentage(true);
            $oSeries->setShowValue(false);
    
            $oBarChart  =  new Pie();
            $oBarChart->addSeries( $oSeries );
            $oShape->getPlotArea()->setType($oBarChart);
            $i++;
        }    
    }

    public function slide5($objPHPPowerPoint, $address){

        $dataRaw = $this->report->params()->where('report_id', $this->report->id)
            ->where('address', $address)
            ->whereIn('param',['site','route','call','show-org','show_org'])
            ->get()->groupBy('type');
        $data=[];

        foreach($dataRaw as $key => $types){
            $key = config("yandex-config.periods.$key");
            foreach( collect($types)->groupBy('param') as $service => $params){
                foreach($params as $item => $string){
                    if(!isset($data[$key][config("yandex-config.params_translate.$service")])){
                        $data[$key][config("yandex-config.params_translate.$service")] = 0;
                    }
                    $data[$key][config("yandex-config.params_translate.$service")] += $string->value;
                }
            }
        }
        $data = collect($data)->sortKeys()->toArray();
    
        $currentSlide = $objPHPPowerPoint->createSlide();

        $this->create_slide_template($currentSlide);

        $this->create_slide_title($currentSlide, 'Сводная статистика Яндекс.Профиля');

        $this->create_address_string($currentSlide, $address);

        $i=0;

        foreach($data as $key => $param){

            $oLine = new Line();
            $oGridLines = new Gridlines();
            $oGridLines->getOutline()->setWidth(10);
            $oGridLines->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(new Color(Color::COLOR_BLUE));
                        
            $oShape = $currentSlide->createChartShape()
                        ->setHeight(500)
                        ->setWidth(460)
                        ->setOffsetX(101 + ($i * 460))
                        ->setOffsetY(190);
            $oShape->getPlotArea()->getAxisX()->setMajorGridlines($oGridLines);
            $oShape->getTitle()->setVisible(false);
            $oShape->getLegend()->setPosition('b');

            $color = new Color('ff750be8');
            $series  =  new Series($key, $param); 
            $series->setLabelPosition(Series::LABEL_OUTSIDEEND);
            $series->getFill()->setFillType('solid')->setStartColor($color);
            $oBarChart  =  new Bar();
            $oBarChart->addSeries( $series ); 
    
            $oBarChart->setBarGrouping( Bar::GROUPING_CLUSTERED ); 
            $oBarChart->setOverlapWidthPercent(-25);
    
            $oShape->getPlotArea()->getAxisX()->setTitle('');
            $oShape->getPlotArea()->getAxisY()->setTitle('');
            $oShape->getPlotArea()->setType($oBarChart);
            $i++;
        }    
    }

    public function slide6($objPHPPowerPoint, $address){

        $dataRaw = $this->report->devices()->where('report_id', $this->report->id)
            ->where('address', $address)
            ->get();

        $data=[];
        foreach($dataRaw as $string ){
            $data[config("yandex-config.devices_translate.$string->param")] = $string->value;
        }

        $dataRaw2 = $this->report->deviceManufactures()->where('report_id', $this->report->id)
        ->where('address', $address)
        ->get();

        $data2=[];
        $total2=[
            'manufacture' => '',
            'value' => 0,
            'summary' => 0,
        ];
        foreach($dataRaw2 as $string ){
            $data2[$string->param] = $string->value;
        }
        
        foreach($data2 as $key => $param){
            $total2['summary'] += $param;
            if($param > $total2['value']){
                $total2['manufacture'] = $key;
                $total2['value'] = $param;
            }
        }

        $currentSlide = $objPHPPowerPoint->createSlide();

        $this->create_slide_template($currentSlide);

        $this->create_slide_title($currentSlide, 'Типы устройств');

        $this->create_address_string($currentSlide, $address);

        $percent = (($total2['value'] / $total2['summary'])) *100;
        $percent = round($percent,0);
        $text='';

        if($percent > 50 && $total2['manufacture'] == 'Apple'){
            $text = "iOS - $percent%";
        }else{
            $p = 100 - $percent;
            $text = "OC Android - $p%";
        }

        $shape = $currentSlide->createRichTextShape()
                    ->setHeight(500)
                    ->setWidth(300)
                    ->setOffsetX(700)
                    ->setOffsetY(190);
        $shape->getActiveParagraph()->setSpacingAfter(32)->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $textRun = $shape->createTextRun("Владельцы марки - ". $total2['manufacture'] . " занимают $percent% от общего количества посетителей, что является наибольшим значением среди других производителей.");
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(16)
                    ->setColor( new Color( '000' ) );

        $shape->createParagraph()->setSpacingAfter(32);
        $textRun = $shape->createTextRun("Основными посетителями являются пользователи  смартфонов с $text");
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(16)
                    ->setColor( new Color( '000' ) );
        
        //Диаграма 1 устройства
        
        $oLine = new Line();
        $oGridLines = new Gridlines();
        $oGridLines->getOutline()->setWidth(10);
        $oGridLines->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color(Color::COLOR_BLUE));
                    
        $oShape = $currentSlide->createChartShape()
                    ->setHeight(400)
                    ->setWidth(300)
                    ->setOffsetX(60)
                    ->setOffsetY(190);
        $oShape->getPlotArea()->getAxisX()->setMajorGridlines($oGridLines);
        $oShape->getTitle()->setWidth(500)->setText('Тип устройства')->getAlignment()->setHorizontal('ctr');
        $oShape->getTitle()->getFont()->setSize(14);

        $seriesData1 = $data;

        
        $oSeries1  =  new Series('Тип устройства', $seriesData1); 
        $oSeries1->setLabelPosition(Series::LABEL_OUTSIDEEND);

        $oBarChart  =  new Pie();
        $oBarChart->addSeries( $oSeries1 );
        $oShape->getPlotArea()->setType($oBarChart);
        $oShape->getLegend()->setPosition('b');


        //Диаграма 2 тип ОС

        $oLine = new Line();
        $oGridLines = new Gridlines();
        $oGridLines->getOutline()->setWidth(10);
        $oGridLines->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color(Color::COLOR_BLUE));
                    
        $oShape = $currentSlide->createChartShape()
                    ->setHeight(400)
                    ->setWidth(300)
                    ->setOffsetX(350)
                    ->setOffsetY(190);
        $oShape->getPlotArea()->getAxisX()->setMajorGridlines($oGridLines);
        $oShape->getTitle()->setWidth(500)->setText('Смартфоны')->getAlignment()->setHorizontal('ctr');
        $oShape->getTitle()->getFont()->setSize(14);

        $seriesData1 = $data2;
        
        $oSeries1  =  new Series('Смартфоны', $seriesData1); 
        $oSeries1->setLabelPosition(Series::LABEL_OUTSIDEEND);
        $oSeries1->setShowPercentage(true);
        $oSeries1->setShowValue(false);

        $oBarChart  =  new Pie();
        $oBarChart->addSeries( $oSeries1 );
        $oShape->getPlotArea()->setType($oBarChart);
        $oShape->getLegend()->setPosition('b');

    }

    public function slide7($objPHPPowerPoint){
   
        $currentSlide = $objPHPPowerPoint->createSlide();

        $this->create_slide_template($currentSlide);

        $this->create_slide_title($currentSlide, 'Анализ');

    }

    public function create_slide_title($currentSlide, $text){
        $shape = $currentSlide->createRichTextShape()
            ->setHeight(100)
            ->setWidth(1000)
            ->setOffsetX(61)
            ->setOffsetY(50);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
        $textRun = $shape->createTextRun($text);
        $textRun->setLanguage('ru-RU')->getFont()->setName('Dela Gothic One')->setBold(false)
                    ->setSize(32)
                    ->setColor( new Color( '000' ) );
    
    }

    public function create_slide_template($currentSlide){
        $oBkgImage = new Image();
        $oBkgImage->setPath(\storage_path(). '/app/master-slide-bg.png');
        $currentSlide->setBackground($oBkgImage); 
    }

    public function create_address_string($currentSlide, $address){
            $shape = $currentSlide->createRichTextShape()
                    ->setHeight(100)
                    ->setWidth(1000)
                    ->setOffsetX(61)
                    ->setOffsetY(120);
            $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
            $textRun = $shape->createTextRun($address);
            $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(24)
                        ->setColor( new Color( '000' ) );
    }

    public function current_period(){
        return Carbon::createFromFormat('Y-m-d', $this->report->date_start)->translatedFormat('F Y');
    }

    public function previous_period(){
        return Carbon::createFromFormat('Y-m-d', $this->report->date_start)->subMonths(1)->translatedFormat('F Y');
    }
    
}