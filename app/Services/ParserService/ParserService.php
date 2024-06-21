<?php
namespace App\Services\ParserService;

use DOMXPath;
use DOMDocument;

class ParserService
{
    public function parserFeedbackCount($url):int | bool
    {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $result = curl_exec($ch);
        $dom = new DOMDocument();
        if(empty($result)){
            return false;
        }
        libxml_use_internal_errors(true);
        $dom->loadHTML($result);
        libxml_use_internal_errors(false);

        $xpath = new DOMXPath($dom);
        $node = $xpath->query("//*[contains(@class, '_name_reviews')]")->item(0);
        if($node){ 
            return (int)preg_replace( '/[^0-9]/', '', $node->textContent );
        }
        return false;
    
    }

}