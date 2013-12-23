<?php

namespace Pim\Bundle\InstallerBundle\Tests\Unit\FixtureLoader;

use Pim\Bundle\InstallerBundle\FixtureLoader\MultipleLoader;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultipleLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $configurationRegistry = $this
            ->getMock('Pim\Bundle\InstallerBundle\FixtureLoader\ConfigurationRegistryInterface');
        $factory = $this->getMockBuilder('Pim\Bundle\InstallerBundle\FixtureLoader\LoaderFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $multipleLoader = new MultipleLoader($configurationRegistry, $factory);
        $configurationRegistry
            ->expects($this->any())
            ->method('contains')
            ->will(
                $this->returnValueMap(
                    array(
                        array('entity1', true),
                        array('entity2', true),
                        array('entity3', true),
                        array('entity4', false)
                    )
                )
            );
        $configurationRegistry
            ->expects($this->any())
            ->method('getOrder')
            ->will(
                $this->returnValueMap(
                    array(
                        array('entity1', 150),
                        array('entity2', 100),
                        array('entity3', 100)
                    )
                )
            );

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $referenceRepository = $this->getMockBuilder('Doctrine\Common\DataFixtures\ReferenceRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $loader = $this->getMock('Pim\Bundle\InstallerBundle\FixtureLoader\LoaderInterface');
        $factory->expects($this->any())
            ->method('create')
            ->with(
                $this->identicalTo($objectManager),
                $this->identicalTo($referenceRepository),
                $this->anything(),
                $this->anything()
            )
            ->will($this->returnValue($loader));

        $loader->expects($this->at(0))
            ->method('load')
            ->with($this->equalTo('/dir1/entity2.csv'));

        $loader->expects($this->at(1))
            ->method('load')
            ->with($this->equalTo('/dir2/entity3.yml'));

        $loader->expects($this->at(2))
            ->method('load')
            ->with($this->equalTo('/dir1/entity1.yml'));

        $multipleLoader->load(
            $objectManager,
            $referenceRepository,
            array(
                '/dir1/entity1.yml',
                '/dir1/entity2.csv',
                '/dir2/entity3.yml',
                '/dir4/entity4.csv'
            )
        );
    }
}
