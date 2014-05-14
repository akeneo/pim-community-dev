<?php

namespace Pim\Bundle\InstallerBundle\Tests\Unit\FixtureLoader;

use Pim\Bundle\InstallerBundle\FixtureLoader\LoaderFactory;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoaderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $doctrineCache = $this->getMockBuilder('Pim\Bundle\TransformBundle\Cache\DoctrineCache')
            ->disableOriginalConstructor()
            ->getMock();
        $configurationRegistry = $this->getMock(
            'Pim\Bundle\InstallerBundle\FixtureLoader\ConfigurationRegistryInterface'
        );

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $referenceRepository = $this
            ->getMockBuilder('Doctrine\Common\DataFixtures\ReferenceRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineCache->expects($this->once())
            ->method('setReferenceRepository')
            ->with($this->identicalTo($referenceRepository));

        $reader = $this->getMock('Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
        $configurationRegistry->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('name'), $this->equalTo('extension'))
            ->will($this->returnValue($reader));

        $processor = $this->getMock('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $configurationRegistry->expects($this->once())
            ->method('getProcessor')
            ->with($this->equalTo('name'), $this->equalTo('extension'))
            ->will($this->returnValue($processor));

        $configurationRegistry->expects($this->once())
            ->method('getClass')
            ->with($this->equalTo('name'))
            ->will($this->returnValue('Pim\Bundle\InstallerBundle\FixtureLoader\Loader'));

        $eventSubscriber = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $productManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();

        $configurationRegistry->expects($this->once())
            ->method('getProductManager')
            ->will($this->returnValue($productManager));

        $factory = new LoaderFactory(
            $doctrineCache,
            $configurationRegistry,
            $eventSubscriber
        );
        $result = $factory->create($objectManager, $referenceRepository, 'name', 'extension');
        $this->assertInstanceOf('Pim\Bundle\InstallerBundle\FixtureLoader\Loader', $result);
    }
}
