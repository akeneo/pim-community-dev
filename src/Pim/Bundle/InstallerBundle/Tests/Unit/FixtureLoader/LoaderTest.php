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
        $referenceRepository = $this->getMockBuilder('Doctrine\Common\DataFixtures\ReferenceRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $entityCache = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\EntityCache')
            ->disableOriginalConstructor()
            ->getMock();
        $reader = $this->getMockBuilder('Oro\Bundle\BatchBundle\Item\ItemReaderInterface')
            ->setMethods(array('setFilePath', 'read'))
            ->getMock();
        $processor = $this->getMock('Oro\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $eventSubscriber = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $loader = new Loader($objectManager, $referenceRepository, $entityCache, $reader, $processor, $eventSubscriber);

        $reader->expects($this->once())
            ->method('setFilePath')
            ->with($this->equalTo('file'));

        $data1 = array('code'=>'data1');
        $data2 = array('code'=>'data2');
        $reader->expects($this->at(1))
            ->method('read')
            ->will($this->returnValue($data1));
        $reader->expects($this->at(2))
            ->method('read')
            ->will($this->returnValue($data2));
        $reader->expects($this->at(3))
            ->method('read')
            ->will($this->returnValue(null));

        $object1 = new \stdClass;
        $object2 = new \stdClass;
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

        $referenceRepository->expects($this->at(0))
            ->method('addReference')
            ->with($this->equalTo('stdClass.data1'));
        $referenceRepository->expects($this->at(1))
            ->method('addReference')
            ->with($this->equalTo('stdClass.data2'));
        $loader->load('file');
    }
}
