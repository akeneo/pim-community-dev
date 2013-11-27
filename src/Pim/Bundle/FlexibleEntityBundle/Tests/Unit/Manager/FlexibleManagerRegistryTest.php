<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityManager;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $entityFQCN = 'Pim\\Bundle\\FlexibleEntityBundle\\Test\\Entity\\Demo';

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
        $this->flexibleManager =  $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot get flexible manager for class "Pim\Bundle\FlexibleEntityBundle\Test\Entity\Demo".
     */
    public function testGetManagerFails()
    {
        $this->registry->getManager($this->entityFQCN);
    }
}
