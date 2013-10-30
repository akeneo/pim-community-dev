<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Cache;

use Pim\Bundle\ImportExportBundle\Cache\EntityCache;

/**
 * Tests EntityCache
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testFind()
    {
        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->exactly(2))
            ->method('findOneBy')
            ->will($this->returnArgument(0));

        $doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $doctrine->expects($this->exactly(2))
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $cache = new EntityCache($doctrine);
        $this->assertEquals(array('code' => 'object1'), $cache->find('class', 'object1'));
        $this->assertEquals(array('code' => 'object2'), $cache->find('class', 'object2'));

        //Test that the values are not queried a second time
        $this->assertEquals(array('code' => 'object1'), $cache->find('class', 'object1'));
        $this->assertEquals(array('code' => 'object2'), $cache->find('class', 'object2'));
    }

    public function testReset()
    {
        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->exactly(4))
            ->method('findOneBy')
            ->will($this->returnArgument(0));

        $doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $doctrine->expects($this->exactly(4))
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $cache = new EntityCache($doctrine);
        $this->assertEquals(array('code' => 'object1'), $cache->find('class', 'object1'));
        $this->assertEquals(array('code' => 'object2'), $cache->find('class', 'object2'));

        $cache->clear();
        //Test that the values are not queried a second time
        $this->assertEquals(array('code' => 'object1'), $cache->find('class', 'object1'));
        $this->assertEquals(array('code' => 'object2'), $cache->find('class', 'object2'));
    }
}
