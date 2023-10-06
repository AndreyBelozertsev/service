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

class SitePresentation extends AbstractPresentationType implements PresentationCreateInterface
{

    public function create(){
        $this->objPHPPowerPoint->removeSlideByIndex(0);
    
        $this->slide1($this->objPHPPowerPoint);

        $this->slide2();

        $this->slide3();

        $this->slide4();

        $this->slide5();

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

    public function slide2(){
        $currentSlide = $this->objPHPPowerPoint->createSlide();

        $dataRaw = $this->report->goals()
                                ->where('report_id', $this->report->id)
                                ->where('type', 'current')
                                ->get();

        $data = [];

        foreach($dataRaw as $key => $param){
            $value = 0;
            if(isset($data[$param['param']])){
                $value = $data[$param['param']]; 
            }
       
            $data[$param['param']] = $value + $param->value;
        }
        
        $data = collect($data)->sortDesc()->toArray();

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'Сводная статистика сайта');
        
        
        $oLine = new Line();
        $oGridLines = new Gridlines();
        $oGridLines->getOutline()->setWidth(10);
        $oGridLines->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(new Color(Color::COLOR_BLUE));
                    
        $oShape = $currentSlide->createChartShape()
                    ->setHeight(500)
                    ->setWidth(900)
                    ->setOffsetX(101)
                    ->setOffsetY(190);
        $oShape->getPlotArea()->getAxisX()->setMajorGridlines($oGridLines);
        $oShape->getTitle()->setVisible(false);
        $oShape->getLegend()->setPosition('b');

        $oBarChart  =  new Bar();
    

        $color = new Color(config("yandex-config.report_colors.2"));
        $series  =  new Series('Сайт', $data); 
        $series->setLabelPosition(Series::LABEL_OUTSIDEEND);
        $series->getFill()->setFillType('solid')->setStartColor($color);
        $oBarChart->addSeries( $series ); 
 
    
        
        $oBarChart->setBarGrouping( Bar::GROUPING_CLUSTERED ); 
        $oBarChart->setOverlapWidthPercent(-25);

        $oShape->getPlotArea()->getAxisX()->setTitle('');
        $oShape->getPlotArea()->getAxisY()->setTitle('');
        $oShape->getPlotArea()->setType($oBarChart);
            

    }


    public function slide3(){
        $currentSlide = $this->objPHPPowerPoint->createSlide();

        $dataRaw = $this->report->goalDevices()
                    ->where('report_id', $this->report->id)
                    ->get()->groupBy('param');
                    
 
        $data=[];

        $total = [];
  
        foreach($dataRaw as $key => $param){

           $data[$key] = [];
           foreach($param as $string){
                $keyTranslate = config("yandex-config.devices_translate." . $string['source']);
                $data[$key][$keyTranslate] = $string['value'];
                $totalValue = 0;
                if(isset($total[$string['param']])){
                    $totalValue = $total[$string['param']];
                }
                $total[$string['param']] = $totalValue + $string['value'];
            } 
        }
        
        $total = collect($total)->sortDesc()->slice(0,1)->toArray();

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'Цели по типу устройства');


        $shape = $currentSlide->createRichTextShape()
                    ->setHeight(500)
                    ->setWidth(400)
                    ->setOffsetX(650)
                    ->setOffsetY(190);
        $shape->getActiveParagraph()->setSpacingAfter(16)->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $textRun = $shape->createTextRun('На этом графике можно увидеть, какие действия и с каких устройств пользователи совершают, перейдя на сайт.');
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(16)
                    ->setColor( new Color( '000' ) );
        
        $shape->createParagraph()->setSpacingAfter(16);
        $textRun = $shape->createTextRun('Наиболее популярное действие, за '. $this->currentPeriod() . ' является - ' . key($total));
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(16)
                    ->setColor( new Color( '000' ) );
        
        if(count($data)){
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
    
    }

    public function slide4(){

        $dataRaw = $this->report->goals()
                                ->where('report_id', $this->report->id)
                                ->where('type', 'current')
                                ->where('value','>', 0)
                                ->whereNotIn('param', ['Визиты', 'Посетители'])
                                ->get();
        $dataSummVisited = $this->report->goals()
                            ->where('report_id', $this->report->id)
                            ->where('type', 'current')
                            ->where('param','Визиты')
                            ->sum('value');

        $summ = $dataRaw->sum('value');

        $data=[];
        $total=[];
        $total['value'] = 0;
        $total['key'] = 'undefined';
        foreach($dataRaw as $key => $string){
            $totalValue = 0;
            if(isset($data[$string['param']])){
                $totalValue = $data[$string['param']];
            }
            $data[$string['param']] = $totalValue + $string['value'];
            if($data[$string['param']] > $total['value']){
                $total['value'] = $data[$string['param']];
                $total['key'] = $string['param'];
            }
        }

        $currentSlide = $this->objPHPPowerPoint->createSlide();

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'CR профиля');

        $shape = $currentSlide->createRichTextShape()
                    ->setHeight(500)
                    ->setWidth(500)
                    ->setOffsetX(600)
                    ->setOffsetY(190);
        $shape->getActiveParagraph()->setSpacingAfter(16)->getAlignment()->setHorizontal( Alignment::HORIZONTAL_LEFT );
        $textRun = $shape->createTextRun('Целевые действия');
        $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                    ->setSize(16)
                    ->setColor( new Color( '000' ) );
        if($summ > 0){
            $shape->createParagraph()->setSpacingAfter(16);
            $textRun = $shape->createTextRun(round($total['value']/$summ * 100) . '% от количества целевых действий – это ' . $total['key']);
            $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(16)
                        ->setColor( new Color( '000' ) );
        }
        if($dataSummVisited > 0){
            $shape->createParagraph()->setSpacingAfter(16);
            $textRun = $shape->createTextRun(round($total['value']/$dataSummVisited * 100, 2) . '% визитов закончилось достижением этой цели.');
            $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(16)
                        ->setColor( new Color( '000' ) );
        }

        if(count($data)){
            $oLine = new Line();
            $oGridLines = new Gridlines();
            $oGridLines->getOutline()->setWidth(10);
            $oGridLines->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(new Color(Color::COLOR_BLUE));
                        
            $oShape = $currentSlide->createChartShape()
                        ->setHeight(170)
                        ->setWidth(480)
                        ->setOffsetX(101)
                        ->setOffsetY(190);
            $oShape->getPlotArea()->getAxisX()->setMajorGridlines($oGridLines);
            $oShape->getLegend()->setOffsetX(150);
            
            
            $oSeries  =  new Series('Достижение цели', $data); 
            $oSeries->setLabelPosition(Series::LABEL_OUTSIDEEND);
            $oSeries->setShowPercentage(true);
            $oSeries->setShowValue(false);

            $oBarChart  =  new Pie();
            $oBarChart->addSeries( $oSeries );
            $oShape->getPlotArea()->setType($oBarChart);
        }
    
    }

    public function slide5(){
   
        $currentSlide = $this->objPHPPowerPoint->createSlide();

        $this->createSlideTemplate($currentSlide);

        $this->createSlideTitle($currentSlide, 'Анализ');

    }
}
