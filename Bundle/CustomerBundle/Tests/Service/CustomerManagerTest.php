<?php
namespace Oro\Bundle\CustomerBundle\Test\Service;

use Oro\Bundle\FlexibleEntityBundle\Tests\KernelAwareTest;
use Oro\Bundle\CustomerBundle\Entity\Customer;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class CustomerManagerTest extends KernelAwareTest
{

    /**
     * @var FlexibleEntityManager
     */
    protected $manager;

    /**
     * UT set up
     */
    public function setUp()
    {
        parent::setUp();
        $this->manager = $this->container->get('customer_manager');
    }

    /**
     * Test related method
     */
    public function testcreateEntity()
    {
        $newCustomer = $this->manager->createEntity();
        $this->assertTrue($newCustomer instanceof Customer);
        $newCustomer->setFirstname('Nicolas');
        $newCustomer->setLastname('Dupont');
        $this->assertEquals($newCustomer->getFirstname(), 'Nicolas');
    }
}
