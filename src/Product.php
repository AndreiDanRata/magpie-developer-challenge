<?php

namespace App;

class Product
{
    
    public static function getProduct($elementCrawler) {

        //Get the product name
        $name = $elementCrawler->filter('span.product-name')->text();  

        //Get the product capacity
        $capacity = $elementCrawler->filter('span.product-capacity')->text();

        //Check capacity unit of measure and convert to MB if necessary
        if (strpos($capacity, 'MB') === false) {
            $capacityMB = (int) $capacity *1000;
        } else {
            $capacityMB = $capacity;
        }

        //Set title 
        $title = $name." ".$capacity;



        //Get price
        $price = $elementCrawler -> filter('div.my-8.block.text-center.text-lg') -> text();
        $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);


        $imageUrl = $elementCrawler-> filter('.my-8.mx-auto') -> image() -> getUri();


        //Get availability text
        $availability = $elementCrawler -> filter('div.my-4.text-sm.block.text-center');
        $availabilityText = $availability -> eq(0) -> text();
        $availabilityText = str_replace("Availability: ", "", $availabilityText);

        //Check if it is out of stock
        $isAvailable = $availabilityText == "Out of Stock" ? false : true;


        //If avaiblable extract shipping text + shipping date(if present)
        if(count($availability) == 2){
            
            $shippingText = $availability -> eq(1) -> text();
            $shippingDate =  ScrapeHelper :: extractDate($shippingText);
        } else {

            $shippingText = null;
            $shippingDate = null;
        }


        //Create an array with the same product in diffrerent colors
        $productOptions = [];
        $colors = $elementCrawler -> filter('div.px-2 > span') -> extract(['data-colour']);
        //$colors = $elementCrawler -> filter("span[data-colour]");
        //print_r($colors);
        foreach($colors as $color) {

            $product = array();
            $product['title'] = $title;
            $product['price'] = $price;
            $product['imageUrl'] = $imageUrl;
            $product['capacityMB'] = $capacityMB;
            $product['colour'] = $color;
            $product['availabilityText'] = $availabilityText;
            $product['isAvailable'] = $isAvailable;
            $product['shippingText'] = $shippingText;
            $product['shippingDate'] = $shippingDate;


            array_push($productOptions, $product);
        }

        //print_r($name . " " . $capacity);
       // print_r($colorOptions);


        return $productOptions;
    }

}
