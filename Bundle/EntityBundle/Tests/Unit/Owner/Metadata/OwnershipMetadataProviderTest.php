<?php

namespace Oro\Bundle\EntityBundle\Tests\Unit\Owner\Metadata;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadata;

class OwnershipMetadataProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testOwnerClassesConfig()
    {
        $entityClassResolver = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\EntityClassResolver')
            ->disableOriginalConstructor()
            ->getMock();
        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $entityClassResolver->expects($this->exactly(3))
            ->method('getEntityClass')
            ->will(
                $this->returnValueMap(
                    array(
                        array('AcmeBundle:Organization', 'AcmeBundle\Entity\Organization'),
                        array('AcmeBundle:BusinessUnit', 'AcmeBundle\Entity\BusinessUnit'),
                        array('AcmeBundle:User', 'AcmeBundle\Entity\User'),
                    )
                )
            );

        $provider = new OwnershipMetadataProvider(
            array(
                'organization' => 'AcmeBundle:Organization',
                'business_unit' => 'AcmeBundle:BusinessUnit',
                'user' => 'AcmeBundle:User',
            ),
            $configProvider,
            $entityClassResolver
        );

        $this->assertEquals('AcmeBundle\Entity\Organization', $provider->getOrganizationClass());
        $this->assertEquals('AcmeBundle\Entity\BusinessUnit', $provider->getBusinessUnitClass());
        $this->assertEquals('AcmeBundle\Entity\User', $provider->getUserClass());
    }

    public function testOwnerClassesConfigWithoutEntityClassResolver()
    {
        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new OwnershipMetadataProvider(
            array(
                'organization' => 'AcmeBundle\Entity\Organization',
                'business_unit' => 'AcmeBundle\Entity\BusinessUnit',
                'user' => 'AcmeBundle\Entity\User',
            ),
            $configProvider
        );

        $this->assertEquals('AcmeBundle\Entity\Organization', $provider->getOrganizationClass());
        $this->assertEquals('AcmeBundle\Entity\BusinessUnit', $provider->getBusinessUnitClass());
        $this->assertEquals('AcmeBundle\Entity\User', $provider->getUserClass());
    }

    public function testGetMetadataUndefinedClassWithoutCache()
    {
        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new OwnershipMetadataProvider(
            array(
                'organization' => 'AcmeBundle\Entity\Organization',
                'business_unit' => 'AcmeBundle\Entity\BusinessUnit',
                'user' => 'AcmeBundle\Entity\User',
            ),
            $configProvider,
            null
        );

        $configProvider->expects($this->once())
            ->method('hasConfig')
            ->with($this->equalTo('UndefinedClass'))
            ->will($this->returnValue(false));

        $configProvider->expects($this->never())
            ->method('getConfig');

        $this->assertEquals(
            new OwnershipMetadata(),
            $provider->getMetadata('UndefinedClass')
        );
    }

    public function testGetMetadataWithoutCache()
    {
        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $provider = new OwnershipMetadataProvider(
            array(
                'organization' => 'AcmeBundle\Entity\Organization',
                'business_unit' => 'AcmeBundle\Entity\BusinessUnit',
                'user' => 'AcmeBundle\Entity\User',
            ),
            $configProvider,
            null
        );

        $config = new EntityConfig('SomeClass', 'ownership');
        $config->set('owner_type', 'USER');
        $config->set('owner_field_name', 'test_field');
        $config->set('owner_column_name', 'test_column');

        $configProvider->expects($this->once())
            ->method('hasConfig')
            ->with($this->equalTo('SomeClass'))
            ->will($this->returnValue(true));

        $configProvider->expects($this->once())
            ->method('getConfig')
            ->with($this->equalTo('SomeClass'))
            ->will($this->returnValue($config));

        $this->assertEquals(
            new OwnershipMetadata('USER', 'test_field', 'test_column'),
            $provider->getMetadata('SomeClass')
        );
    }

    public function testGetMetadataUndefinedClassWithCache()
    {
        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $cache = $this->getMockForAbstractClass(
            'Doctrine\Common\Cache\CacheProvider',
            array(),
            '',
            false,
            true,
            true,
            array('fetch', 'save')
        );

        $provider = new OwnershipMetadataProvider(
            array(
                'organization' => 'AcmeBundle\Entity\Organization',
                'business_unit' => 'AcmeBundle\Entity\BusinessUnit',
                'user' => 'AcmeBundle\Entity\User',
            ),
            $configProvider,
            null,
            $cache
        );

        $metadata = new OwnershipMetadata();

        $configProvider->expects($this->once())
            ->method('hasConfig')
            ->with($this->equalTo('UndefinedClass'))
            ->will($this->returnValue(false));

        $configProvider->expects($this->never())
            ->method('getConfig');

        $cache->expects($this->at(0))
            ->method('fetch')
            ->with($this->equalTo('UndefinedClass'))
            ->will($this->returnValue(false));
        $cache->expects($this->at(2))
            ->method('fetch')
            ->with($this->equalTo('UndefinedClass'))
            ->will($this->returnValue($metadata));
        $cache->expects($this->once())
            ->method('save')
            ->with($this->equalTo('UndefinedClass'), $this->equalTo($metadata));

        $this->assertEquals(
            $metadata,
            $provider->getMetadata('UndefinedClass')
        );

        // One another call of getMetadata to check that cache is used
        $this->assertEquals(
            $metadata,
            $provider->getMetadata('UndefinedClass')
        );
    }

    public function testGetMetadataWithCache()
    {
        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $cache = $this->getMockForAbstractClass(
            'Doctrine\Common\Cache\CacheProvider',
            array(),
            '',
            false,
            true,
            true,
            array('fetch', 'save')
        );

        $provider = new OwnershipMetadataProvider(
            array(
                'organization' => 'AcmeBundle\Entity\Organization',
                'business_unit' => 'AcmeBundle\Entity\BusinessUnit',
                'user' => 'AcmeBundle\Entity\User',
            ),
            $configProvider,
            null,
            $cache
        );

        $config = new EntityConfig('SomeClass', 'ownership');
        $config->set('owner_type', 'USER');
        $config->set('owner_field_name', 'test_field');
        $config->set('owner_column_name', 'test_column');

        $metadata = new OwnershipMetadata('USER', 'test_field', 'test_column');

        $configProvider->expects($this->once())
            ->method('hasConfig')
            ->with($this->equalTo('SomeClass'))
            ->will($this->returnValue(true));

        $configProvider->expects($this->once())
            ->method('getConfig')
            ->with($this->equalTo('SomeClass'))
            ->will($this->returnValue($config));

        $cache->expects($this->at(0))
            ->method('fetch')
            ->with($this->equalTo('SomeClass'))
            ->will($this->returnValue(false));
        $cache->expects($this->at(2))
            ->method('fetch')
            ->with($this->equalTo('SomeClass'))
            ->will($this->returnValue($metadata));
        $cache->expects($this->once())
            ->method('save')
            ->with($this->equalTo('SomeClass'), $this->equalTo($metadata));

        $this->assertEquals(
            $metadata,
            $provider->getMetadata('SomeClass')
        );

        // One another call of getMetadata to check that cache is used
        $this->assertEquals(
            $metadata,
            $provider->getMetadata('SomeClass')
        );
    }

    public function testClearCache()
    {

        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $cache = $this->getMockForAbstractClass(
            'Doctrine\Common\Cache\CacheProvider',
            array(),
            '',
            false,
            true,
            true,
            array('delete')
        );

        $provider = new OwnershipMetadataProvider(
            array(
                'organization' => 'AcmeBundle\Entity\Organization',
                'business_unit' => 'AcmeBundle\Entity\BusinessUnit',
                'user' => 'AcmeBundle\Entity\User',
            ),
            $configProvider,
            null,
            $cache
        );

        $cache->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('SomeClass'));

        $provider->clearCache('SomeClass');
    }

    public function testClearCacheAll()
    {

        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $cache = $this->getMockForAbstractClass(
            'Doctrine\Common\Cache\CacheProvider',
            array(),
            '',
            false,
            true,
            true,
            array('deleteAll')
        );

        $provider = new OwnershipMetadataProvider(
            array(
                'organization' => 'AcmeBundle\Entity\Organization',
                'business_unit' => 'AcmeBundle\Entity\BusinessUnit',
                'user' => 'AcmeBundle\Entity\User',
            ),
            $configProvider,
            null,
            $cache
        );

        $cache->expects($this->once())
            ->method('deleteAll');

        $provider->clearCache();
    }
}
