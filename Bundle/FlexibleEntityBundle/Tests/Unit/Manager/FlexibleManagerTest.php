<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Manager;

use Oro\Bundle\FlexibleEntityBundle\Tests\Unit\AbstractFlexibleManagerTest;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class FlexibleManagerTest extends AbstractFlexibleManagerTest
{
    /**
     * test related method
     */
    public function testConstructWithCustomEntityManager()
    {
        $myManager = new FlexibleManager(
            $this->flexibleClassName,
            $this->container->getParameter('oro_flexibleentity.flexible_config'),
            $this->entityManager,
            $this->container->get('event_dispatcher'),
            $this->attributeTypeFactory
        );
        $this->assertNotNull($myManager->getStorageManager());
        $this->assertEquals($myManager->getStorageManager(), $this->entityManager);
    }

    /**
     * test related method
     */
    public function testGetStorageManager()
    {
        $this->assertNotNull($this->manager->getStorageManager());
        $this->assertTrue($this->manager->getStorageManager() instanceof EntityManager);
    }

    /**
     * test related method
     */
    public function testGetFlexibleConfig()
    {
        $this->assertNotNull($this->manager->getFlexibleConfig());
        $this->assertNotEmpty($this->manager->getFlexibleConfig());
        $this->assertEquals(
            $this->manager->getFlexibleConfig(),
            $this->flexibleConfig['entities_config'][$this->flexibleClassName]
        );
    }

    /**
     * Test related method
     */
    public function testGetLocale()
    {
        // get default locale
        $this->assertEquals($this->manager->getLocale(), $this->defaultLocale);
        // forced locale
        $code = 'fr_FR';
        $this->manager->setLocale($code);
        $this->assertEquals($this->manager->getLocale(), $code);
    }

    /**
     * Test related method
     */
    public function testGetScope()
    {
        // get default scope
        $this->assertEquals($this->manager->getScope(), $this->defaultScope);
        // forced scope
        $code = 'ecommerce';
        $this->manager->setScope($code);
        $this->assertEquals($this->manager->getScope(), $code);
    }

    /**
     * Test related method
     */
    public function testGetAttributeName()
    {
        $this->assertEquals($this->manager->getAttributeName(), $this->attributeClassName);
    }

    /**
     * Test related method
     */
    public function testGetAttributeOptionName()
    {
        $this->assertEquals($this->manager->getAttributeOptionName(), $this->attributeOptionClassName);
    }

    /**
     * Test related method
     */
    public function testGetAttributeOptionValueName()
    {
        $this->assertEquals($this->manager->getAttributeOptionValueName(), $this->attributeOptionValueClassName);
    }

    /**
     * Test related method
     */
    public function testGetFlexibleValueName()
    {
        $this->assertEquals($this->manager->getFlexibleValueName(), $this->flexibleValueClassName);
    }

    /**
     * Test related method
     */
    public function testGetFlexibleRepository()
    {
        $this->assertTrue($this->manager->getFlexibleRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testGetAttributeRepository()
    {
        $this->assertTrue($this->manager->getAttributeRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testGetAttributeOptionRepository()
    {
        $this->assertTrue($this->manager->getAttributeOptionRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testGetAttributeOptionValueRepository()
    {
        $this->assertTrue(
            $this->manager->getAttributeOptionValueRepository() instanceof \Doctrine\ORM\EntityRepository
        );
    }

    /**
     * Test related method
     */
    public function testGetFlexibleValueRepository()
    {
        $this->assertTrue($this->manager->getFlexibleValueRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testCreateAttribute()
    {
        $this->assertTrue($this->manager->createAttribute('oro_flexibleentity_text') instanceof $this->attributeClassName);
    }

    /**
     * Test related method
     */
    public function testCreateAttributeOption()
    {
        $this->assertTrue($this->manager->createAttributeOption() instanceof $this->attributeOptionClassName);
    }

    /**
     * Test related method
     */
    public function testCreateAttributeOptionValue()
    {
        $this->assertTrue($this->manager->createAttributeOptionValue() instanceof $this->attributeOptionValueClassName);
    }

    /**
     * Test related method
     */
    public function testCreateFlexible()
    {
        $this->markTestSkipped('Issue with post load event mock');
        $this->assertTrue($this->manager->createFlexible() instanceof $this->flexibleClassName);
    }

    /**
     * Test related method
     */
    public function testCreateFlexibleValue()
    {
        $this->assertTrue($this->manager->createFlexibleValue() instanceof $this->flexibleValueClassName);
    }
}
