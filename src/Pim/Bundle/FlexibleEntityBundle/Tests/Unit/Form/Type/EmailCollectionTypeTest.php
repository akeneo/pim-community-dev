<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Form\Type;

use Pim\Bundle\FlexibleEntityBundle\Form\Type\EmailCollectionType;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;

/**
 * Test related class
 */
class EmailCollectionTypeTest extends TypeTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->type = new EmailCollectionType();
        $this->form = $this->factory->create($this->type);
    }

    /**
     * Test build of form with form type
     */
    public function testFormCreate()
    {
        $this->assertField('collection', 'collection');

        $this->assertEquals('pim_flexibleentity_email_collection', $this->form->getName());
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
