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



        // Delivers Thursday 25th Aug 2022
        // Order within 6 hours and have it 27 Aug 2022
        // Delivers 25 Aug 2022
        // Delivery by Friday 26th Aug 2022


        //Case when date shows tomorrow
        if(str_contains($shippingText, 'tomorrow'))
        {
              $date = new DateTime('tomorrow');
              return $date->format('Y-m-d');
        
        }elseif($shippingText === 'Free Delivery' || $shippingText === 'Free Shipping' ) {
                return null;
        }
        else {
            
            //remove all words that are not months
        


            //Case when date matches the format: 03/03/2012 , 30-01-22 , 1 2 2005
            //$datePattern = '/\d{4}-\d{1,2}-\d{1,2}/';
            $datePattern = '/([0-9]?[0-9])[\.\-\/ ]+([0-1]?[0-9])[\.\-\/ ]+([0-9]{2,4})/';
            preg_match( $datePattern, $shippingText, $matches );
            if ($matches) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
            } else {

                $datePattern = '/\d{1,2}([a-z]{2})?\s[A-Z][a-z]{2}\s\d{4}/';
                preg_match($datePattern, $shippingText, $shippingDate);

                //Case when date matches the format: 25 Aug 2022, 1st Sep 2022
                
                //Remove ordinal numbers from the date: 'st', 'nd', 'rd', 'th'

                $splitDate = explode(" ", $shippingDate[0]);

                

                $day = $splitDate[0];

                $day = preg_replace('/[a-z]{2}/' ,'' ,$day);
    
                //Replaces day '1' '5' to '01', '05'
                if(array_key_exists($day, $daysMap)) {

                    $day = $daysMap[$day];
                }
                            
                //Replaces 'Jan', 'Feb' to '01', '02'
                $month = $monthsMap[$splitDate[1]];
                
                $year = $splitDate[2];
                
            }

            //Concatenate string into correct date format
            $shippingDate = $year . "-" . $month . "-" . $day;


        }

        

        return $shippingDate;
    }


}
