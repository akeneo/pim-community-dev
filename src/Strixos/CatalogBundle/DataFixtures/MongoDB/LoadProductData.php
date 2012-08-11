<?php
// src/Strixos/CatalogBundle/DataFixtures/MongoDB/LoadProductData.php

namespace Strixos\CatalogBundle\DataFixtures\MongoDB;

use Strixos\CatalogBundle\Document\Product;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Strixos\CatalogBundle\Entity\AttributeSet;
use Strixos\CatalogBundle\Entity\Attribute;

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
        $product = new Product();
        $product->setAttributeSetCode('tshirt-straight');
        $product->setSku('foobar-tshirt-1');
        $product->addValue('name', 'My t-shirt');
        $product->addValue('short_description', 'My t-shirt foo bar lorem ipsum');
        $product->addValue('tshirt-straight', 'M');
        $manager->persist($product);
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