<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeTypeTest extends KernelAwareTest
{

    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function testBuildForm()
    {
        // text field
        $productManager = $this->container->get('pim.catalog.product_manager');
        $attClass = $productManager->getAttributeClass();
        $optClass = $productManager->getAttributeOptionClass();
        $entity = $productManager->getNewAttributeInstance();
        $this->container->get('form.factory')->create(new ProductAttributeType($attClass, $optClass), $entity);

        // select field
        $productManager = $this->container->get('pim.catalog.product_manager');
        $entity = $productManager->getNewAttributeInstance();
        $entity->setType(BaseFieldFactory::FIELD_SELECT);
        $this->container->get('form.factory')->create(new ProductAttributeType($attClass, $optClass), $entity);
    }

}
