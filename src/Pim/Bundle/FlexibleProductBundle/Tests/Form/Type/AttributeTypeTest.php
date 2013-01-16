<?php
namespace Pim\Bundle\FlexibleProductBundle\Tests\Form\Type;

use Pim\Bundle\FlexibleProductBundle\Form\Type\AttributeType;

use Pim\Bundle\TestBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AttributeTypeTest extends KernelAwareTest
{

    /**
     * Expected fields in attribute type
     * @staticvar multitype:string
     */
    public static $expectedFields = array(
        'id',
        'code',
        'backend_type',
        'backend_storage',
        'required',
        'unique',
        'default_value',
        'searchable',
        'translatable',
        'scopable'
    );

    /**
     * Test related method
     */
    public function testBuildForm()
    {
        // get classes full name and create attribute
        $attClassFullname     = $this->getProductManager()->getAttributeName();
        $prodAttClassFullname = $this->getProductManager()->getFlexibleAttributeName();

        $attribute = $this->getProductManager()->createAttribute();

        // create form
        $form = $this->container->get('form.factory')->create(
            new AttributeType($attClassFullname),
            $attribute
        );

        // assert form
        $this->assertCount(count(self::$expectedFields), $form->all());
        foreach ($form->all() as $child) {
            $this->assertTrue(in_array($child->getName(), self::$expectedFields));
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