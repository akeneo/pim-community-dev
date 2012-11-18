<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
use Pim\Bundle\UIBundle\Grid\Helper as GridHelper;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Source\Document as GridDocument;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HelperTest extends KernelAwareTest
{

    /**
     * Test related method
     */
    public function testGetGridSource()
    {
        $productManager = $this->container->get('pim.catalog.product_manager');

        // test ORM
        $om = $this->container->get('doctrine.orm.entity_manager');
        $source = GridHelper::getGridSource($om, $productManager->getEntityShortName());
        $this->assertTrue($source instanceof GridEntity);

        // test ODM
        $om = $this->container->get('doctrine.odm.mongodb.document_manager');
        $source = GridHelper::getGridSource($om, $productManager->getEntityShortName());
        $this->assertTrue($source instanceof GridDocument);
    }

}
