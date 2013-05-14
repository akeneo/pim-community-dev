<?php
namespace Pim\Bundle\ProductBundle\Tests\Functional\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test segment manager used for ProductSegment entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSegmentManagerTest extends WebTestCase
{

    /**
     * Test instanciation with service
     */
    public function testServiceCall()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $manager = static::$kernel->getContainer()->get('pim_product.classification_tree_manager');

        $this->assertInstanceOf('Oro\Bundle\SegmentationTreeBundle\Manager\SegmentManager', $manager);
    }
}
