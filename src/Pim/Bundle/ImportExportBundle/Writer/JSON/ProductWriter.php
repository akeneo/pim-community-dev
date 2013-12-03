<?php
namespace Pim\Bundle\ImportExportBundle\Writer\JSON;

use Pim\Bundle\ImportExportBundle\Writer\JSON\JSONWriter;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductPoster
 *
 * @author wn-s.rascar
 */
class ProductWriter extends JSONWriter
{

    protected function generateBody($items)
    {
       // php5.5 return json_encode(array_column($items, 'productsArray'));
        $productsArray = array();
        foreach ($items as $item){
            $productsArray = array_merge($productsArray, $item['products']);
        }
        return json_encode($productsArray);
    }
}
