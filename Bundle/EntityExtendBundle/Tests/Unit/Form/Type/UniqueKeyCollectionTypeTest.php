<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\TypeTestCase;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityExtendBundle\Form\Type\UniqueKeyCollectionType;

class UniqueKeyCollectionTypeTest extends TypeTestCase
{
    protected $type;
    protected $fields;

    protected function setUp()
    {
        parent::setUp();

        $this->fields = array(
            new FieldConfigId('Oro\Bundle\UserBundle\Entity\User', 'entity', 'firstName', 'string'),
            new FieldConfigId('Oro\Bundle\UserBundle\Entity\User', 'entity', 'lastName', 'string'),
            new FieldConfigId('Oro\Bundle\UserBundle\Entity\User', 'entity', 'email', 'string'),
        );

        $this->type = new UniqueKeyCollectionType($this->fields);
    }

    public function testType()
    {
        $formData = array(
            'keys' => array(
                'tag0' => array(
                    'name' => 'test key 1',
                    'key'  => array()
                ),
                'tag1' => array(
                    'name' => 'test key 2',
                    'key'  => array()
                )
            )
        );

        $form = $this->factory->create($this->type);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $this->assertEquals($formData, $form->getData());
    }

    public function testNames()
    {
        $this->assertEquals('oro_entity_extend_unique_key_collection_type', $this->type->getName());
    }
}
