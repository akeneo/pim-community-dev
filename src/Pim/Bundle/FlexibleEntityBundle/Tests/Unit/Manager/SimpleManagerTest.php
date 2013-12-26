<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Manager;

use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\AbstractOrmTest;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\FlexibleEntityBundle\Manager\SimpleManager;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleManagerTest extends AbstractOrmTest
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        parent::setUp();
        // prepare simple entity manager (use default entity manager)
        $this->entityName = 'Pim\Bundle\FlexibleEntityBundle\Tests\Unit\\Entity\Demo\Simple';
        $this->manager = new SimpleManager($this->entityName, $this->container->get('doctrine.orm.entity_manager'));
    }

    /**
     * test related method
     */
    public function testConstructWithCustomEntityManager()
    {
        $myManager = new SimpleManager($this->entityName, $this->entityManager);
        $this->assertNotNull($myManager->getObjectManager());
        $this->assertEquals($myManager->getObjectManager(), $this->entityManager);
    }

    /**
     * test related method
     */
    public function testGetObjectManager()
    {
        $this->assertNotNull($this->manager->getObjectManager());
        $this->assertTrue($this->manager->getObjectManager() instanceof EntityManager);
    }

    /**
     * test related method
     */
    public function testGetEntityName()
    {
        $this->assertEquals($this->manager->getEntityName(), $this->entityName);
    }

    /**
     * test related method
     */
    public function testGetEntityRepository()
    {
        $this->assertTrue($this->manager->getEntityRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * test related method
     */
    public function testCreateEntity()
    {
        $this->assertTrue($this->manager->createEntity() instanceof $this->entityName);
    }
}
