<?php

namespace Pim\Bundle\InstallerBundle\Tests\Unit\FixtureLoader;

use Pim\Bundle\InstallerBundle\FixtureLoader\Loader;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $doctrineCache = $this->getMockBuilder('Pim\Bundle\TransformBundle\Cache\DoctrineCache')
            ->disableOriginalConstructor()
            ->getMock();
        $reader = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface')
            ->setMethods(['setFilePath', 'read'])
            ->getMock();
        $processor = $this->getMock('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $eventSubscriber = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $loader = new Loader(
            $objectManager,
            $doctrineCache,
            $reader,
            $processor,
            $eventSubscriber,
            false
        );

        $reader->expects($this->once())
            ->method('setFilePath')
            ->with($this->equalTo('file'));

        $data1 = ['code' => 'data1'];
        $data2 = ['code' => 'data2'];
        $reader->expects($this->at(1))
            ->method('read')
            ->will($this->returnValue($data1));
        $reader->expects($this->at(2))
            ->method('read')
            ->will($this->returnValue($data2));
        $reader->expects($this->at(3))
            ->method('read')
            ->will($this->returnValue(null));

        $object1 = $this->getMockObject('data1');
        $object2 = $this->getMockObject('data2');
        $processor->expects($this->at(0))
            ->method('process')
            ->with($this->equalTo($data1))
            ->will($this->returnValue($object1));
        $processor->expects($this->at(1))
            ->method('process')
            ->with($this->equalTo($data2))
            ->will($this->returnValue($object2));

        $objectManager->expects($this->at(0))
            ->method('persist')
            ->with($this->identicalTo($object1));
        $objectManager->expects($this->at(1))
            ->method('persist')
            ->with($this->identicalTo($object2));

        $loader->load('file');
    }

    protected function getMockObject($id)
    {
        $object = $this->getMock('Pim\Bundle\CatalogBundle\Model\ReferableInterface');
        $object->expects($this->any())
            ->method('getReference')
            ->will($this->returnValue($id));

        return $object;
    }
}
