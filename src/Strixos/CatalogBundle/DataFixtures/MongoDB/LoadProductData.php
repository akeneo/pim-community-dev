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
        $attSets = array(
            LoadAttributeSetData::ATTRIBUTE_SET_TSHIRT,
            LoadAttributeSetData::ATTRIBUTE_SET_LAPTOP
        );
        for ($ind = 0; $ind <= 10000; $ind++) {
            $product = new Product();
            // get random set
            $attSetInd = rand(0, 1);
            $attSetCode = $attSets[$attSetInd];
            $product->setAttributeSetCode($attSetCode);
            // define default values
            $product->setSku('foobar-'.$ind);
            $product->addValue('name', 'My t-shirt '.$ind);
            $product->addValue('short_description', 'My t-shirt foo bar lorem ipsum'.$ind);

            // define specific values
            if ($attSetCode == LoadAttributeSetData::ATTRIBUTE_SET_TSHIRT) {
                $product->addValue(LoadAttributeSetData::ATTRIBUTE_TSHIRT_COLOR, 'Red');
                $product->addValue(LoadAttributeSetData::ATTRIBUTE_TSHIRT_SIZE, 'M');
            } else if ($attSetCode == LoadAttributeSetData::ATTRIBUTE_SET_LAPTOP) {
                $product->addValue(LoadAttributeSetData::ATTRIBUTE_LAPTOP_CPU, 'I7');
                $product->addValue(LoadAttributeSetData::ATTRIBUTE_LAPTOP_HDD, 'Sata 200 GO');
                $product->addValue(LoadAttributeSetData::ATTRIBUTE_LAPTOP_MEMORY, '8 GO');
                $product->addValue(LoadAttributeSetData::ATTRIBUTE_LAPTOP_SCREEN, '15"');
            }
            $manager->persist($product);
        }
        $manager->flush();
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