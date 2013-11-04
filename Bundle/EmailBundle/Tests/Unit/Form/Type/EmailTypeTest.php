<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\PreloadedExtension;
use Oro\Bundle\EmailBundle\Form\Type\EmailType;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Form\Type\EmailAddressType;

class EmailTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $emailAddressType = new EmailAddressType();

        return array(
            new PreloadedExtension(
                array(
                    $emailAddressType->getName() => $emailAddressType,
                ),
                array()
            )
        );
    }

    public function testSubmitValidData()
    {
        $formData = array(
            'gridName' => 'test_grid',
            'from'     => 'John Smith <john@example.com>',
            'to'       => 'John Smith 1 <john1@example.com>; "John Smith 2" <john2@example.com>; john3@example.com',
            'subject'  => 'Test subject',
            'body'     => 'Test body',
        );

        $type = new EmailType();
        $form = $this->factory->create($type);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        /** @var Email $result */
        $result = $form->getData();
        $this->assertInstanceOf('Oro\Bundle\EmailBundle\Form\Model\Email', $result);
        $this->assertEquals('test_grid', $result->getGridName());
        $this->assertEquals('John Smith <john@example.com>', $result->getFrom());
        $this->assertEquals(
            array('John Smith 1 <john1@example.com>', '"John Smith 2" <john2@example.com>', 'john3@example.com'),
            $result->getTo()
        );
        $this->assertEquals('Test subject', $result->getSubject());
        $this->assertEquals('Test body', $result->getBody());

        $view     = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'data_class'      => 'Oro\Bundle\EmailBundle\Form\Model\Email',
                    'intention'       => 'email',
                    'csrf_protection' => true,
                    'cascade_validation' => true
                )
            );

        $type = new EmailType(array());
        $type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $type = new EmailType(array());
        $this->assertEquals('oro_email_email', $type->getName());
    }
}
