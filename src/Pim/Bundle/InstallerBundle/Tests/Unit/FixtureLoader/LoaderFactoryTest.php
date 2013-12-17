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
        $entityCache = $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Cache\EntityCache')
            ->disableOriginalConstructor()
            ->getMock();
        $fixtureReferenceTransformer = $this
            ->getMockBuilder('Pim\Bundle\InstallerBundle\Transformer\Property\FixtureReferenceTransformer')
            ->disableOriginalConstructor()
            ->getMock();
        $configurationRegistry = $this->getMock('Pim\Bundle\InstallerBundle\FixtureLoader\ConfigurationRegistryInterface');

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $referenceRepository = $this
            ->getMockBuilder('Doctrine\Common\DataFixtures\ReferenceRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $fixtureReferenceTransformer->expects($this->once())
            ->method('setReferenceRepository')
            ->with($this->identicalTo($referenceRepository));

        $reader = $this->getMock('Oro\Bundle\BatchBundle\Item\ItemReaderInterface');
        $configurationRegistry->expects($this->once())
            ->method('getReader')
            ->with($this->equalTo('name'), $this->equalTo('extension'))
            ->will($this->returnValue($reader));

        $processor = $this->getMock('Oro\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $configurationRegistry->expects($this->once())
            ->method('getProcessor')
            ->with($this->equalTo('name'), $this->equalTo('extension'))
            ->will($this->returnValue($processor));

        $configurationRegistry->expects($this->once())
            ->method('getClass')
            ->with($this->equalTo('name'))
            ->will($this->returnValue('Pim\Bundle\InstallerBundle\FixtureLoader\Loader'));

        $factory = new LoaderFactory($entityCache, $fixtureReferenceTransformer, $configurationRegistry);
        $result = $factory->create($objectManager, $referenceRepository, 'name', 'extension');
        $this->assertInstanceOf('Pim\Bundle\InstallerBundle\FixtureLoader\Loader', $result);
    }
}
