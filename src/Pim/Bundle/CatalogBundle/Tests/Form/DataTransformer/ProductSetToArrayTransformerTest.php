<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductSetToArrayTransformer;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSetToArrayTransformerTest extends KernelAwareTest
{

    /**
     * Test related method
     */
    public function testTransform()
    {
        // get first set
        $productManager = $this->container->get('pim.catalog.product_manager');
        $set = $productManager->getSetRepository()->findAll()->getSingleResult();
        $this->assertNotNull($set);
        // transform to array
        $transformer = new ProductSetToArrayTransformer($productManager);
        $data = $transformer->transform($set);
        // assert
        $this->assertEquals($set->getCode(), $data['code']);
        $this->assertEquals($set->getTitle(), $data['title']);
        $this->assertEquals($set->getGroups()->count(), count($data['groups']));
    }

    /**
     * Test related method
     */
    public function testReverseTransform()
    {
        $productManager = $this->container->get('pim.catalog.product_manager');

        // transform array to new set
        $transformer = new ProductSetToArrayTransformer($productManager);
        $data = array (
            'id'     => null,
            'code'   => 'new-set',
            'title'  => 'my title',
            'groups' => array(
                'new-group' => array(
                    'id'    => null,
                    'code'  => 'new-group',
                    'title' => 'new-title',
                    'attributes' => array()
                )
            )
        );
        $set = $transformer->reverseTransform($data);
        // assert
        /*
        $this->assertEquals($set->getCode(), $data['code']);
        $this->assertEquals($set->getTitle(), $data['title']);
        $this->assertEquals($set->getGroupByCode('new-group')->getTitle(), $data['groups']['new-group']['title']);
        */
    }

}
