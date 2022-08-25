<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];

    const  WEBSITE_URL = 'https://www.magpiehq.com/developer-challenge/smartphones';


    public function run(): void
    {
    
        
        $document = ScrapeHelper::fetchDocument(self::WEBSITE_URL);

        $numOfPages = $this->getNumOfPages($document);

        for($i = 1; $i <= $numOfPages; $i++) {

            $document = ScrapeHelper::fetchDocument(self::WEBSITE_URL.'/?page='.$i);

            $items = $document -> filter("div.product.px-4");
            
        
            foreach($items as $element) {
                
                $elementCrawler = new Crawler($element,'https://www.magpiehq.com/developer-challenge/smartphones/?page='. $i); 
                // $elementCrawler = new Crawler($element -> ownerDocument -> saveHTML($element),
				// 'https://www.magpiehq.com/developer-challenge/smartphones/?page='. $i);
                
                $productOptions = Product::getProduct($elementCrawler);
            

                //check each color variant if it is already in the products array and add it in case it is not present
                foreach($productOptions as $colorOption) {
                    if (in_array($colorOption,$this->products) === false) {
                        $this->products[] = $colorOption;
                    }
                }

        
            }
        }

    

        file_put_contents('output.json', json_encode($this->products, JSON_PRETTY_PRINT ));
    }

    //method returns the total number of pages
    private function getNumOfPages($document) {

        $value = $document -> filter('#products')->text();
        preg_match_all('!\d+!', $value, $matches);
        $test =  $matches[0];
        $numOfPages = (int) $test[1];
        return $numOfPages;
    }

}


$scrape = new Scrape();
$scrape->run();
