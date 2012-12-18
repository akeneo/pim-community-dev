<?php
namespace Oro\Bundle\ProductBundle\Test\Service;

use Oro\Bundle\ProductBundle\Entity\ProductEntity;

use Oro\Bundle\DataModelBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductManagerTest extends KernelAwareTest
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
        $this->manager = $this->container->get('product_manager');
    }

    /**
     * Test related method
     */
    public function testGetNewEntityInstance()
    {
        $newProduct = $this->manager->getNewEntityInstance();
        $this->assertTrue($newProduct instanceof ProductEntity);

        $sku = 'my sku '.str_replace('.', '', microtime(true));
        $newProduct->setSku('my sku');

        $this->manager->getPersistenceManager()->persist($newProduct);
        $this->manager->getPersistenceManager()->flush();
    }
}
