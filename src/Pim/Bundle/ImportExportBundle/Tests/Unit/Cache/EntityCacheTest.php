<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Cache;

/**
 * Tests EntityCache
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testFind()
    {
        $cache = $this->createCache(2);
        $this->assertEquals('object1', $cache->find('class', 'object1'));
        $this->assertEquals('object2', $cache->find('class', 'object2'));

        //Test that the values are not queried a second time
        $this->assertEquals('object1', $cache->find('class', 'object1'));
        $this->assertEquals('object2', $cache->find('class', 'object2'));
    }

    /**
     * Test related method
     */
    public function testReset()
    {
        $cache = $this->createCache(4);

        $this->assertEquals('object1', $cache->find('class', 'object1'));
        $this->assertEquals('object2', $cache->find('class', 'object2'));

        $cache->clear();
        //Test that the values are not queried a second time
        $this->assertEquals('object1', $cache->find('class', 'object1'));
        $this->assertEquals('object2', $cache->find('class', 'object2'));
    }

    /**
     * @param integer $queryCount
     *
     * @return \Pim\Bundle\ImportExportBundle\Cache\EntityCache
     */
    protected function createCache($queryCount)
    {
        $doctrine = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $cache = $this->getMockForAbstractClass(
            'Pim\Bundle\ImportExportBundle\Cache\EntityCache',
            array($doctrine),
            '',
            true,
            true,
            true,
            array('getEntity')
        );
        $cache->expects($this->exactly($queryCount))
            ->method('getEntity')
            ->will($this->returnArgument(1));

        return $cache;
    }
}
