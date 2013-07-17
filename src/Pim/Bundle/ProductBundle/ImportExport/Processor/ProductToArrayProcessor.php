<?php

namespace Pim\Bundle\ProductBundle\ImportExport\Processor;

use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;

/**
 * Very basic sample transformer that will put the first letter of each item in uppercase
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductToArrayProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $productAsArray = array();
        $sku = $item->getData('sku');
        $attributes = array();
        foreach($item->getValues() as $value) {
            $attrCode = $value->getAttribute()->getCode();
            $attributes[$attrCode] = array(
                "value" =>
                    array(
                        "data" => $value->getData(),
                        "scope" => $value->getScope(),
                        "locale" => $value->getLocale()
                    );


            if ($value->getTranslatable()) {
                $locales = array();

                $productAsArray[$sku]["attributes"][$attrCode] = array("locales" => array();
                
                if ($value->getAttribute()->getScopable() ) {
                    $productAsArray[$sku][$attrCode][$value->getLocale][$value->getScope] = $value->getData();
                } else {
                    $productAsArray[$sku][$attrCode][$value->getLocale] = $value->getData();
                }
            } else 
                if ($value->getAttribute()->getScopable() ) {
                    $productAsArray[$sku][$attrCode][$value->getLocale][$value->getScope] = $value->getData();
                } else {
                    $productAsArray[$sku][$attrCode][$value->getLocale] = $value->getData();
                }
            }

            try {
                echo "Attribute code:".$value->getAttribute()->getCode()."\n"
                    ."\t Locale:".$value->getLocale()."\n"
                    ."\t Scope:".$value->getScope()."\n"
                    ."\t Value:".$value->getData()."\n";
            } catch (\Exception $e) {
                echo "Not stringable\n";
            }
        }
        $productAsArray[$sku] = array("attributes" => $attributes);
    }
}
