<?php 

namespace App\Services\PresentationCreate\Types;

use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Shape\Chart\Series;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Bar;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Pie;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Line;
use \PhpOffice\PhpPresentation\Shape\Chart\Gridlines;
use PhpOffice\PhpPresentation\Slide\Background\Image;
use App\Services\PresentationCreate\Types\AbstractPresentationType;
use App\Services\PresentationCreate\Contract\PresentationCreateInterface;

class ProfilePresentation extends AbstractPresentationType implements PresentationCreateInterface
{
    protected $dataSlide2 = [];

    protected $dataSlide4 = [];


    protected $actionList = [
        'site' => 0, 
        'call' => 0, 
        'route' => 0, 
        'social' => 0
    ];

    public function __construct($report)
	{
        parent::__construct($report);
    
        $this->addresses = $this->report->params()
        ->select('address')
        ->where('report_id', $this->report->id)
        ->distinct('address')
        ->get()->toArray();
    }

    public function create(){
        $this->objPHPPowerPoint->removeSlideByIndex(0);
    
        $this->slide1($this->objPHPPowerPoint);

        foreach($this->addresses as $address){
            $this->slide2($address['address']);
        }

        foreach($this->addresses as $address){
            $this->slide3($address['address']);
        }

        foreach($this->addresses as $address){
            $this->slide4($address['address']);
        }

        foreach($this->addresses as $address){
            $this->slide5($address['address']);
        }

        foreach($this->addresses as $address){
           $this->slide6($address['address']);
        }


        foreach($this->addresses as $address){
            $this->slide7($address['address']);
         }

        $this->slide8();

        return $this->makeFile();
    }



    public function slide1(){
        $currentSlide = $this->objPHPPowerPoint->createSlide();

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
        $textRun->setLanguage('ru-RU')->getFont()->setName('Calibri')->setBold(false)
                        ->setSize(60)
                        ->setColor( new Color( '000' ) );

        $shape = $currentSlide->createRichTextShape()
                ->setHeight(100)
                ->setWidth(600)
                ->setOffsetX(261.25)
                ->setOffsetY(550);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
        $textRun = $shape->createTextRun($this->currentPeriod());
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

    public function slide2($address){
        $currentSlide = $this->objPHPPowerPoint->createSlide();
        $dataRaw = $this->report->params()->where('report_id', $this->report->id)
                                ->where('address', $address)
                                ->whereIn('param',['show-org','show_org','view_org_content'])
                                ->get()->groupBy('type');


        $data = [];
        $total = [];
        $services = [];

        foreach($dataRaw as $key => $param){
            foreach($param as $string){
                $service = Str::replace('.', '', $string['service']);
                $servTranslate = config("yandex-config.services.$service");
                if(! isset($this->dataSlide4[$address][$servTranslate]['total'] )){
                    $this->dataSlide4[$address][$servTranslate]['total'] = 0;
                }
                if(! isset($this->dataSlide4[$address][$servTranslate]['params'] )){
                    $this->dataSlide4[$address][$servTranslate]['params'] = $this->actionList;
                }
                
                $services[] = $servTranslate;
            }
        }
        $uniqueServices = collect($services)->unique();
        
        foreach($dataRaw as $key => $param){
    
            $key = Str::replace('.', '', $key);
            $key = config("yandex-config.periods.$key");
            $total[$key]=0;
           foreach($param as $string){
                $service = Str::replace('.', '', $string['service']);
                $servTranslate = config("yandex-config.services.$service");
                $services[] = $servTranslate;
                if(! isset($data[$key][$servTranslate])){
                    $data[$key][$servTranslate] = 0;
                }
                $data[$key][$servTranslate] += $string->value;

                if($key == 'Текущий период'){
                    $this->dataSlide4[$address][$servTranslate]['total'] += $string->value;
                }
                $total[$key] += $string->value;
            }
            foreach($uniqueServices as $service){
                if(! isset($data[$key][$service])){
                    $data[$key][$service] = 0;
                }
            }
            $data[$key] = collect($data[$key])->sortKeys()->toArray();
        }
        $data = collect($data)->sortKeys()->toArray();
        $uniqueServices = collect($services)->unique();

        $word = '';
        $percent = 0;

        if(isset($total['Предыдущий период']) && isset($total['Текущий период'])){
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
        if(isset($data['Текущий период'])){

            $this->dataSlide2[$address] = $data['Текущий период'];
        
        }

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'Переходы в профиль из сервисов Яндекс');

        $this->createAddressString($currentSlide, $address);

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
        $textRun = $shape->createTextRun('Количество переходов в профиля за ' . $this->currentPeriod() . $word);
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


    public function slide3($address){
        $currentSlide = $this->objPHPPowerPoint->createSlide();

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
                if(isset($dataRaw['Текущий период']) && $string['type']  == 'current'){
                    $service = Str::replace('.', '', $string->service);
                    $servTranslate = config("yandex-config.services.$service");
                    $this->dataSlide4[$address][$servTranslate]['params'][$string->param] += $string->value;
                }
            }
            
            if(isset($dataRaw['Текущий период']) && $dataRaw['Текущий период'] > $totalValue){
                $totalValue = $dataRaw['Текущий период'];
                $total = $key; 
            }
            $data[$key] = $dataRaw;
        }

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'Действия в профиле');

        $this->createAddressString($currentSlide, $address);


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
        $textRun = $shape->createTextRun('Наиболее популярное действие, за '. $this->currentPeriod() . ' является - ' . config("yandex-config.params_translate.$total"));
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

    public function slide4($address){
        $currentSlide = $this->objPHPPowerPoint->createSlide();

        $dataRaw = $this->dataSlide4[$address];
        $data = [];
        $cr = [];
        $total = 0;
        $totalCr = 0;
        foreach($dataRaw as $key => $value){
            $total += $value['total'];
            foreach($value['params'] as $param => $string){
                $data[config("yandex-config.params_translate.$param")][$key] = $string;
                if(!isset($cr[config("yandex-config.params_translate.$param")])){
                    $cr[config("yandex-config.params_translate.$param")] = 0;
                }
                $cr[config("yandex-config.params_translate.$param")] += $string;
            }
        }

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'СR в профиле');

        $this->createAddressString($currentSlide, $address);


        $shape = $currentSlide->createRichTextShape()
                    ->setHeight(500)
                    ->setWidth(400)
                    ->setOffsetX(650)
                    ->setOffsetY(190);
        $shape->getActiveParagraph()->setSpacingAfter(16)->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );

        foreach($cr as $action => $value){
            $crCurrent = round(($value/$total)*100, 2);
            $totalCr += $crCurrent;
            $shape->createParagraph()->setSpacingAfter(16);
            $textRun = $shape->createTextRun($crCurrent . '% - ' . $action);
            $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(16)
                        ->setColor( new Color( '000' ) );
        }
        $shape->getActiveParagraph()->setSpacingAfter(16)->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $shape->createParagraph()->setSpacingAfter(16);
        $textRun = $shape->createTextRun($totalCr . '% - Общий CR профиля');
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

    public function slide5($address){
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

        $currentSlide = $this->objPHPPowerPoint->createSlide();

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'CR профиля');

        $this->createAddressString($currentSlide, $address);

        $shape = $currentSlide->createRichTextShape()
                    ->setHeight(500)
                    ->setWidth(520)
                    ->setOffsetX(550)
                    ->setOffsetY(190);
        $shape->getActiveParagraph()->setSpacingAfter(16)->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $textRun = $shape->createTextRun('На этом графике можно увидеть, какие действия пользователи совершают, перейдя в профиль вашей организации.');
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(14)
                    ->setColor( new Color( '000' ) );

        foreach($total as $key => $string){
            $percent = round($string['value']/$string['summary']*100, 0);
            $percentAction = 0;
            if(isset($this->dataSlide2[$address][$string['service']])){
                $percentAction = round($string['value']/$this->dataSlide2[$address][$string['service']]*100, 2);
            }
            
            $key = config("yandex-config.params_translate.$key");
            $shape->createParagraph()->setSpacingAfter(12);
            $textRun = $shape->createTextRun( "$percent% от количества целевого действия \"$key\" происходят в сервисе " . $string['service'] );
            $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(14)
                        ->setColor( new Color( '000' ) );
            $shape->createParagraph()->setSpacingAfter(24);
            $textRun = $shape->createTextRun("$percentAction% визитов,  с сервиса " . $string['service'] . " содержат, достижение цели \"$key\"");
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
                        ->setWidth(420)
                        ->setOffsetX(75)
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

    public function slide6($address){

        $dataRaw = $this->report->params()->where('report_id', $this->report->id)
            ->where('address', $address)
            ->whereIn('param',['site','route','call','show-org','show_org', 'view_org_content'])
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
    
        $currentSlide = $this->objPHPPowerPoint->createSlide();

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'Сводная статистика Яндекс.Профиля');

        $this->createAddressString($currentSlide, $address);

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

    public function slide7($address){

        $dataRaw = $this->report->devices()->where('report_id', $this->report->id)
            ->where('address', $address)
            ->get();
        if($dataRaw->isEmpty()){
            return;
        }
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

        $currentSlide = $this->objPHPPowerPoint->createSlide();

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'Типы устройств');

        $this->createAddressString($currentSlide, $address);
        
        $percent = 0;
        if($total2['summary']){
            $percent = (($total2['value'] / $total2['summary'])) *100;
            $percent = round($percent,0);
        }

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

    public function slide8(){
   
        $currentSlide = $this->objPHPPowerPoint->createSlide();

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'Анализ');

    }

}
