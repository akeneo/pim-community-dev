<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ContainsProductsUpdateGuesser;
use Pim\Bundle\CatalogBundle\Model\Category;
use Pim\Bundle\CatalogBundle\Model\Product;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContainsProductsUpdateGuesserTest extends AbstractUpdateGuesserTest
{
    /**
     * Test related methods
     */
    public function testGuessUpdates()
    {
        $category   = new Category();
        $productOne = new Product();
        $productTwo = new Product();
        $category->addProduct($productOne);
        $category->addProduct($productTwo);

        $guesser   = new ContainsProductsUpdateGuesser();
        $em        = $this->getEntityManagerMock();
        $updates   = $guesser->guessUpdates($em, $category, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(2, count($updates));
        $this->assertEquals($productOne, $updates[0]);
        $this->assertEquals($productTwo, $updates[1]);
    }
}
