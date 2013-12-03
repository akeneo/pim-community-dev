<?php

namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Tests\OrmTestCase;

use Symfony\Component\DependencyInjection\Container;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

/**
 * Test related class
 */
class FlexibleManagerRegistryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Registry
     *
     * @var FlexibleManagerRegistry
     */
    protected $registry;

    /**
     * Manager
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $entityFQCN = 'Oro\\Bundle\\FlexibleEntityBundle\\Test\\Entity\\Demo';

    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var string
     */
    protected $managerId = 'myserviceid';

    /**
     * Set up unit test
     */
    public function setUp()
    {
        $this->registry        = new FlexibleManagerRegistry();
        $this->flexibleManager =  $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test related method
     */
    public function testAddManager()
    {
        $this->assertEquals(count($this->registry->getManagers()), 0);
        $this->registry->addManager($this->managerId, $this->flexibleManager, $this->entityFQCN);
        $this->assertEquals(count($this->registry->getManagers()), 1);
    }

    /**
     * Test related method
     */
    public function testGetEntityToManager()
    {
        $this->assertEquals(count($this->registry->getEntityToManager()), 0);
        $this->registry->addManager($this->managerId, $this->flexibleManager, $this->entityFQCN);
        $this->assertEquals(count($this->registry->getEntityToManager()), 1);
    }

    /**
     * Test related method
     */
    public function testGetManager()
    {
        $this->registry->addManager($this->managerId, $this->flexibleManager, $this->entityFQCN);
        $this->assertEquals($this->registry->getManager($this->entityFQCN), $this->flexibleManager);
    }

    //@codingStandardsIgnoreStart
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot get flexible manager for class "Oro\Bundle\FlexibleEntityBundle\Test\Entity\Demo".
     */
    //@codingStandardsIgnoreEnd
    public function testGetManagerFails()
    {
        $this->registry->getManager($this->entityFQCN);
    }
}
