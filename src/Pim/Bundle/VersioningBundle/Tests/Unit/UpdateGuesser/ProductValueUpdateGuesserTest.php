<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ProductValueUpdateGuesser;
use Pim\Bundle\CatalogBundle\Entity\Product;
use Pim\Bundle\CatalogBundle\Entity\ProductValue;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;

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
        $product  = new Product();
        $value    = new ProductValue();
        $product->addValue($value);

        $guesser   = new ProductValueUpdateGuesser();
        $em        = $this->getEntityManagerMock();
        $updates   = $guesser->guessUpdates($em, $value, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($product, $updates[0]);

        $product  = new Product();
        $value    = new ProductValue();
        $price    = new ProductPrice();
        $value->addPrice($price);
        $product->addValue($value);

        $updates   = $guesser->guessUpdates($em, $price, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($product, $updates[0]);

    }
}
