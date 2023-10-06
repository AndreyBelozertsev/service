<?php 
namespace App\Services\PresentationCreate\Types;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Slide\Background\Image;

abstract class AbstractPresentationType
{

    protected $report;

    public $objPHPPowerPoint;

    public function __construct($report){
        $this->report = $report;
        $this->objPHPPowerPoint = new PhpPresentation();
    }

    public function setReport($report)
    {
        $this->report = $report;
        return $this;
    }

    public function getReport()
    {
        return $this->report;
    }

    public function makePreviousDateStart(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->getReport()->date_start)
                ->startOfMonth()
                ->subMonth()
                ->toDateString();
    }

    public function makePreviousDateEnd(): Carbon
    {
        $date1 = $this->getReport()->date_start;

        $date2 = $this->getReport()->date_end;

        $dateResp = Carbon::createFromFormat('Y-m-d', $date1)
                ->subMonth();
     
        if( Carbon::createFromFormat('Y-m-d', $date2)->endOfMonth() != 
            Carbon::createFromFormat('Y-m-d', $date2)->endOfDay())
        {
            $dateResp->day = Carbon::createFromFormat('Y-m-d', $date2)->day;
        }else{
            $dateResp->endOfMonth();
        }
        return $dateResp->toDateString();
    }

    public function currentPeriod(){
        return Carbon::createFromFormat('Y-m-d', $this->getReport()->date_start)->translatedFormat('F Y');
    }

    public function getPreviousDate1()
    {
        return $this->makePreviousDateStart();
    }

    public function getPreviousDate2()
    {
        return $this->makePreviousDateEnd();
    }

    public function createSlideTitle($currentSlide, $text){
        $shape = $currentSlide->createRichTextShape()
            ->setHeight(100)
            ->setWidth(1000)
            ->setOffsetX(61)
            ->setOffsetY(50);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
        $textRun = $shape->createTextRun($text);
        $textRun->setLanguage('ru-RU')->getFont()->setName('Calibri')->setBold(false)
                    ->setSize(32)
                    ->setColor( new Color( '000' ) );
    }

    public function createSlideTemplate($currentSlide){
        $oBkgImage = new Image();
        $oBkgImage->setPath(\storage_path(). '/app/master-slide-bg.png');
        $currentSlide->setBackground($oBkgImage); 
    }

    public function createAddressString($currentSlide, $address){
            $shape = $currentSlide->createRichTextShape()
                    ->setHeight(100)
                    ->setWidth(1000)
                    ->setOffsetX(61)
                    ->setOffsetY(120);
            $shape->getActiveParagraph()->getAlignment()->setHorizontal( Alignment::HORIZONTAL_CENTER );
            $textRun = $shape->createTextRun($address);
            $textRun->setLanguage('ru-RU')->getFont()->setName('Jura')->setBold(false)
                        ->setSize(16)
                        ->setColor( new Color( '000' ) );
    }

    public function makeFile(){
    
        $this->objPHPPowerPoint->getLayout()->setDocumentLayout('A4');
     
        $oWriterPPTX = IOFactory::createWriter($this->objPHPPowerPoint, 'PowerPoint2007');
        $name = "{$this->report->client->name}-{$this->report->client->type}-{$this->report->date_start}-{$this->report->date_end}.pptx";
        
        if(!Storage::disk('report')->exists('/'. $this->report->id)) {
            Storage::disk('report')->makeDirectory('/'. $this->report->id, 0775, true); //creates directory
        }

        $oWriterPPTX->save(Storage::disk('report')->path($this->report->id . "/$name"));
        return $name;
    }

}