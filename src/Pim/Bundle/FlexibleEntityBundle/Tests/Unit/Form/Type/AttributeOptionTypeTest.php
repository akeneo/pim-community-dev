<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Form\Type;

use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Pim\Bundle\FlexibleEntityBundle\Form\Type\AttributeOptionType;

/**
 * Test related class
 */
class AttributeOptionTypeTest extends TypeTestCase
{

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->type = new AttributeOptionType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('id', 'hidden');
        $this->assertField('sort_order', 'integer');
        $this->assertField('translatable', 'text');

        $this->assertEquals(
            'Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption',
            $this->form->getConfig()->getDataClass()
        );

        $this->assertEquals('pim_flexibleentity_attribute_option', $this->form->getName());
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
}
