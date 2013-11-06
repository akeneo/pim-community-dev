<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Form\Type;

use Pim\Bundle\FlexibleEntityBundle\Form\Type\PriceType;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

/**
 * Test related class
 */
class PriceTypeTest extends TypeTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->type = new PriceType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('id', 'hidden');
        $this->assertField('data', 'number');
        $this->assertField('currency', 'text');

        $this->assertEquals(
            'Pim\Bundle\FlexibleEntityBundle\Entity\Price',
            $this->form->getConfig()->getDataClass()
        );

        $this->assertEquals('pim_flexibleentity_price', $this->form->getName());
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
