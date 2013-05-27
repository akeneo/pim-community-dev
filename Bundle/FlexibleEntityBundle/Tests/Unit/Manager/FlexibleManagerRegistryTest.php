<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Manager;

use Symfony\Component\DependencyInjection\Container;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\Tests\OrmTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class FlexibleManagerRegistryTest extends OrmTestCase
{

    /**
     * Registry
     * @var FlexibleManagerRegistry
     */
    protected $registry;

    /**
     * Manager
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
        $this->container       = new Container();
        $this->registry        = new FlexibleManagerRegistry();
        $this->entityManager   = $this->_getTestEntityManager();
        $entityConfig          = array('entities_config' => array($this->entityFQCN => array()));
        $this->flexibleManager =  new FlexibleManager(
            $this->entityFQCN,
            $entityConfig,
            $this->entityManager,
            new EventDispatcher(),
            new AttributeTypeFactory($this->container)
        );
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
}
