<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use \Datetime;

class ScrapeHelper
{
    public static function fetchDocument(string $url): Crawler
    {
        $client = new Client();

        $response = $client->get($url);

        return new Crawler($response->getBody()->getContents(), $url);
    }

    
    public static function extractDate(string $shippingText) {

        //Array for mapping day values to the right format         
        $daysMap = ["1" => "01", "2" => "02", "3" => "03", "4" => "04", "5" => "05","6" => "06", "7" => "07", "8" => "08", "9" => "09"];

        //Array for mapping month values to the right format         
        $monthsMap = ["Jan" => "01", "Feb" => "02", "Mar" => "03", "Apr" => "04", "May" => "05", "Jun" => "06","Jul" => "07", "Aug" => "08", "Sep" => "09", "Oct" => "10", "Nov" => "11", "Dec" => "12"];

        $day = "";
        $month = "";
        $year = "";

        //Case when date shows tomorrow
        if(str_contains($shippingText, 'tomorrow'))
        {
              $date = new DateTime('tomorrow');
              return $date->format('Y-m-d');
        
        } else {

            //Case when date matches the format: 03/03/2012 , 30-01-22
            $datePattern = '/([0-9]?[0-9])[\-\/]+([0-1]?[0-9])[\-\/]+([0-9]{2,4})/';
            preg_match( $datePattern, $shippingText, $matches );

            if ($matches) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
            } else {

                //Case when date matches the format: 25 Aug 2022, 1st Sep 2022
                $datePattern = '/\d{1,2}([a-z]{2})?\s[A-Z][a-z]{2}\s\d{4}/';
                preg_match($datePattern, $shippingText, $matches);

                if($matches) {
        
                    $splitDate = explode(" ", $matches[0]);

                    $day = $splitDate[0];

                    //Remove ordinal numbers from the date: 'st', 'nd', 'rd', 'th'
                    $day = preg_replace('/[a-z]{2}/' ,'' ,$day);
        
                    //Replaces day '1' '5' to '01', '05'
                    if(array_key_exists($day, $daysMap)) {
                        $day = $daysMap[$day];
                    }
                                
                    //Replaces 'Jan', 'Feb' to '01', '02'
                    $month = $monthsMap[$splitDate[1]];
                    

                    //Replaces with year
                    $year = $splitDate[2];

                } else {    //Case when no date was found: "Free Delivery"
                    return null;
                }
            }
        }
         //Concatenate string into correct date format for all cases when a date was found
         $shippingDate = $year . "-" . $month . "-" . $day;

        return $shippingDate;
    }


}
