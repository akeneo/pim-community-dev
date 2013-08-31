<?php

namespace Pim\Bundle\CatalogBundle\Tests\Functional\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryManagerTest extends WebTestCase
{
    /**
     * Test instanciation with service
     */
    public function testServiceCall()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $manager = static::$kernel->getContainer()->get('pim_product.manager.category');

        $this->assertInstanceOf('Oro\Bundle\SegmentationTreeBundle\Manager\SegmentManager', $manager);
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Manager\CategoryManager', $manager);
    }
}
