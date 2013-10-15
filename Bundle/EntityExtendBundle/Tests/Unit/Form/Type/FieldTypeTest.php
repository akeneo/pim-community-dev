<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;

use Oro\Bundle\EntityExtendBundle\Form\Type\FieldType;
use Oro\Bundle\FormBundle\Form\Extension\DataBlockExtension;

class FieldTypeTest extends TypeTestCase
{
    protected $type;

    protected function setUp()
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new DataBlockExtension())
            ->getFormFactory();

        $this->type = new FieldType();
    }

    public function testType()
    {
        $formData = array(
            'fieldName' => 'new_field',
            'type'      => 'string'
        );

        $form = $this->factory->create($this->type);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData());
    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_extend_field_type', $this->type->getName());
    }
}
