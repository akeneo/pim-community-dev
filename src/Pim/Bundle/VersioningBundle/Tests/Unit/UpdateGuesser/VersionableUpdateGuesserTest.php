<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

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
class VersionableUpdateGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related methods
     */
    public function testGuessUpdates()
    {
        $attribute = new ProductAttribute();
        $attribute->setCode('my code');
        $guesser   = new VersionableUpdateGuesser();
        $em        = $this->getEntityManagerMock();
        $updates   = $guesser->guessUpdates($em, $attribute);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($attribute, $updates[0]);

        $family    = new Family();
        $family->setCode('my code');
        $updates   = $guesser->guessUpdates($em, $family);
        $this->assertEquals(1, count($updates));
        $this->assertEquals($family, $updates[0]);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManagerMock()
    {
        $mock = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
