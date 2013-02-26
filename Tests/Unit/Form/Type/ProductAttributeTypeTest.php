<?php
namespace Pim\Bundle\ProductBundle\Tests\Form\Type;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

use Pim\Bundle\ProductBundle\Form\Type\ProductAttributeType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeTypeTest extends TypeTestCase
{

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // Create form type
        $this->type = new ProductAttributeType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        // Assert fields
        $this->assertField('name', 'text');
        $this->assertField('description', 'textarea');
        $this->assertField('variant', 'choice');
        $this->assertField('smart', 'checkbox');

        $this->assertField('group', 'text');

        $this->assertAttributeType();

        // Assert option class
        $this->assertEquals(
            'Pim\Bundle\ProductBundle\Entity\ProductAttribute',
            $this->form->getConfig()->getDataClass()
        );

        // Assert name
        $this->assertEquals('pim_product_attribute', $this->form->getName());
    }

    /**
     * Assert field name and type
     * @param string $name Field name
     * @param string $type Field type alias
     */
    protected function assertField($name, $type)
    {
        $formType = $this->form->get($name);
        $this->assertInstanceOf('\Symfony\Component\Form\Form', $formType);
        $this->assertEquals($type, $formType->getConfig()->getType()->getInnerType()->getName());
    }

    /**
     * Assert attribute type data
     */
    protected function assertAttributeType()
    {
        $this->assertField('id', 'hidden');
        $this->assertField('code', 'text');
        $this->assertField('attributeType', 'choice');
        $this->assertField('required', 'checkbox');
        $this->assertField('unique', 'checkbox');
        $this->assertField('translatable', 'checkbox');
        $this->assertField('scopable', 'choice');
        $this->assertField('searchable', 'checkbox');
        $this->assertField('default_value', 'text');
    }
}
