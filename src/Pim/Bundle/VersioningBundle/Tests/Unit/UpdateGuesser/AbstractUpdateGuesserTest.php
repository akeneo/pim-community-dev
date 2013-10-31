<?php

namespace Pim\Bundle\VersioningBundle\Tests\Unit\UpdateGuesser;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractUpdateGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    abstract public function testGuessUpdates();

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
