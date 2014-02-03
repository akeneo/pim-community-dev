<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ProductValueUpdateGuesser;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueUpdateGuesserTest extends AbstractUpdateGuesserTest
{
    /**
     * Test related methods
     */
    public function testGuessUpdates()
    {
        $attribute = new Attribute();
        $attribute->setCode('my_attribute');

        $value = new ProductValue();
        $value->setAttribute($attribute);

        $product = new Product();
        $product->addValue($value);

        $guesser   = new ProductValueUpdateGuesser();
        $em        = $this->getEntityManagerMock();
        $updates   = $guesser->guessUpdates($em, $value, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($product, $updates[0]);

        $attribute = new Attribute();
        $attribute->setCode('my_price');

        $price = new ProductPrice();
        $value = new ProductValue();
        $value->setAttribute($attribute);
        $value->addPrice($price);

        $product = new Product();
        $product->addValue($value);

        $updates = $guesser->guessUpdates($em, $price, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($product, $updates[0]);

    }
}
