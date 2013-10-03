<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityExtendBundle\Form\Type\EntityType;
use Oro\Bundle\FormBundle\Form\Extension\DataBlockExtension;

class EntityTypeTest extends TypeTestCase
{
    protected $type;

    protected function setUp()
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new DataBlockExtension())
            ->getFormFactory();

        $this->type = new EntityType();
    }

    public function testType()
    {
        $formData = array(
            'className' => 'NewEntityClassName'
        );

        $form = $this->factory->create($this->type);
        $form->submit($formData);

        $object = new EntityConfigModel();
        $object->setClassName('NewEntityClassName');

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());
    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_extend_entity_type', $this->type->getName());
    }
}
