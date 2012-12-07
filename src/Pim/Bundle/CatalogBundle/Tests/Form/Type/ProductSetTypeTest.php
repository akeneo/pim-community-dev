<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSetTypeTest extends KernelAwareTest
{

    /**
     * Test related method
     */
    public function testBuildForm()
    {
        $productManager = $this->container->get('pim.catalog.product_manager');
        $productTemplateManager = $this->container->get('pim.catalog.product_template_manager');
        $setClass = $productTemplateManager->getEntityClass();
        $grpClass = $productTemplateManager->getGroupClass();
        $attClass = $productManager->getAttributeClass();
        $entity = $productTemplateManager->getNewEntityInstance();
        $this->container->get('form.factory')->create(new ProductSetType($setClass, $grpClass, $attClass, array(), array()), $entity);
    }

}
