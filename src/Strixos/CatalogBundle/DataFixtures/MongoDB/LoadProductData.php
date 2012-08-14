<?php
// src/Strixos/CatalogBundle/DataFixtures/MongoDB/LoadProductData.php

namespace Strixos\CatalogBundle\DataFixtures\MongoDB;

use Strixos\CatalogBundle\Document\Product;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Strixos\CatalogBundle\Entity\AttributeSet;
use Strixos\CatalogBundle\Entity\Attribute;
use Strixos\CatalogBundle\DataFixtures\ORM\LoadAttributeSetData;

/**
 * Execute with "php app/console doctrine:mongodb:fixtures:load"
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProductData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // magento data
        if (false) {

            // get CSV file content
            $filename = '/home/ndupont/export-sample.csv';
            $content = array();
            $handle = fopen($filename, 'r');
            while (!feof($handle) && $line = fgetcsv($handle, 0, ',', '"')) {
                $content[] = $line;
            }
            fclose($handle);
            // retrieve first line which contains attribute codes
            $attCodes = $content[0];
            unset($content[0]);
            // prepare products by fecthing attribute data from csv
            $nbInsert = 0;
            $range = 1000;
            foreach ($content as $productData) {
                $product = new Product();
                $product->setAttributeSetCode(LoadAttributeSetData::ATTRIBUTE_SET_BASE);
                foreach ($attCodes as $indAttCode => $attCode) {
                    if ($attCode == 'sku') {
                        $product->setSku($productData[$indAttCode]);
                    } else {
                        $product->addValue($attCode, $productData[$indAttCode]);
                    }
                }
                $manager->persist($product);
                if ($nbInsert++ == $range) {
                    $manager->flush();
                    $nbInsert = 0;
                }
            }
            $manager->flush();

        // random
        } else {

            $attSets = array(
                LoadAttributeSetData::ATTRIBUTE_SET_TSHIRT,
                LoadAttributeSetData::ATTRIBUTE_SET_LAPTOP
            );
            for ($ind = 0; $ind < 10000; $ind++) {
                $product = new Product();
                // get random set
                $attSetInd = rand(0, count($attSets)-1);
                $attSetCode = $attSets[$attSetInd];
                $product->setAttributeSetCode($attSetCode);
                // define default values
                $product->setSku('foobar-'.$ind);
                $product->addValue('name', 'My '.$attSetCode.' '.$ind);
                $product->addValue('short_description', 'My '.$attSetCode.' foo bar lorem ipsum'.$ind);

                // define specific values
                if ($attSetCode == LoadAttributeSetData::ATTRIBUTE_SET_TSHIRT) {
                    $product->addValue(LoadAttributeSetData::ATTRIBUTE_TSHIRT_COLOR, 'Red');
                    $product->addValue(LoadAttributeSetData::ATTRIBUTE_TSHIRT_SIZE, 'M');
                } else if ($attSetCode == LoadAttributeSetData::ATTRIBUTE_SET_LAPTOP) {
                    $cpuValues = array('I5', 'I7');
                    $cpuValues = $cpuValues[rand(0, count($cpuValues)-1)];
                    $product->addValue(LoadAttributeSetData::ATTRIBUTE_LAPTOP_CPU, $cpuValues);
                    $hddValues = array('IDE 1000 GO', 'IDE 750 GO', 'Sata 200 GO', 'Sata 400 GO');
                    $hddValue = $hddValues[rand(0, count($hddValues)-1)];
                    $product->addValue(LoadAttributeSetData::ATTRIBUTE_LAPTOP_HDD, $hddValue);
                    $product->addValue(LoadAttributeSetData::ATTRIBUTE_LAPTOP_MEMORY, '8 GO');
                    $product->addValue(LoadAttributeSetData::ATTRIBUTE_LAPTOP_SCREEN, '15"');
                }
                $manager->persist($product);
            }
            $manager->flush();
        }
    }

    /**
    * Executing order
    * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
    */
    public function getOrder()
    {
        return 2;
    }

}