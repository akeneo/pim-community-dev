<?php
namespace Oro\Bundle\ProductBundle\Test\Service;

use Oro\Bundle\ManufacturerBundle\Entity\Manufacturer;

use Oro\Bundle\DataModelBundle\Tests\KernelAwareTest;


/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ManufacturerManagerTest extends KernelAwareTest
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
        $this->manager = $this->container->get('manufacturer_manager');
    }

    /**
     * Test related method
     */
    public function testGetNewEntityInstance()
    {
        $newManufacturer = $this->manager->getNewEntityInstance();
        $this->assertTrue($newManufacturer instanceof Manufacturer);
        $newManufacturer->setName('Lenovo');

        $this->manager->getPersistenceManager()->persist($newManufacturer);
        $this->manager->getPersistenceManager()->flush();
    }
}
