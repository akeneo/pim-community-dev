<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\VersionableUpdateGuesser;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionableUpdateGuesserTest extends AbstractUpdateGuesserTest
{
    /**
     * Test related methods
     */
    public function testGuessUpdates()
    {
        $versionables = array(
            'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
            'Pim\Bundle\CatalogBundle\Entity\Family'
        );
        $attribute = new ProductAttribute();
        $attribute->setCode('my code');
        $guesser   = new VersionableUpdateGuesser($versionables);
        $em        = $this->getEntityManagerMock();
        $updates   = $guesser->guessUpdates($em, $attribute, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($attribute, $updates[0]);

        $family    = new Family();
        $family->setCode('my code');
        $updates   = $guesser->guessUpdates($em, $family, UpdateGuesserInterface::ACTION_UPDATE_ENTITY);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($family, $updates[0]);
    }
}
