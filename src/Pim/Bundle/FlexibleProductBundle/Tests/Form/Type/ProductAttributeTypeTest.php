<?php
namespace Pim\Bundle\FlexibleProductBundle\Tests\Form\Type;

use Pim\Bundle\FlexibleProductBundle\Form\Type\ProductAttributeType;

use Pim\Bundle\TestBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductAttributeTypeTest extends KernelAwareTest
{

    /**
     * Expected fields in product attribute type
     * @staticvar multitype:string
     */
    public static $expectedFields = array(
        'id',
        'name',
        'description',
        'smart',
        'attribute'
    );

    /**
     * Test related method
     */
    public function testBuildForm()
    {
        // get classes full name and create attribute
        $attClassFullname     = $this->getProductManager()->getAttributeName();
        $prodAttClassFullname = $this->getProductManager()->getFlexibleAttributeName();

        $productAttribute = $this->getProductManager()->createFlexibleAttribute();

        // create form
        $form = $this->container->get('form.factory')->create(
            new ProductAttributeType($prodAttClassFullname, $attClassFullname),
            $productAttribute
        );

        // assert form
        $this->assertCount(count(self::$expectedFields), $form->all());
        foreach ($form->all() as $child) {
            $this->assertTrue(in_array($child->getName(), self::$expectedFields));
            // assert attribute type
            if ($child->getName() === 'attribute') {
                $this->assertCount(count(AttributeTypeTest::$expectedFields), $child->all());
                foreach ($child->all() as $attribute) {
                    $this->assertTrue(in_array($attribute->getName(), AttributeTypeTest::$expectedFields));
                }
            }
        }
    }

    /**
     * Get product manager
     * @return Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->container->get('pim.flexible_product.product_manager');
    }
}