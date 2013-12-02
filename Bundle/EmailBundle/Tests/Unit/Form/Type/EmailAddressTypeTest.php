<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\TypeTestCase;
use Oro\Bundle\EmailBundle\Form\Type\EmailAddressType;

class EmailAddressTypeTest extends TypeTestCase
{
    public function testSubmitValidDataForSingleAddressForm()
    {
        $formData = ' John Smith <john@example.com> ';

        $type = new EmailAddressType();
        $form = $this->factory->create($type);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        /** @var string $result */
        $result = $form->getData();
        $this->assertEquals('John Smith <john@example.com>', $result);

        $view = $form->createView();
        $this->assertEquals(trim($formData), $view->vars['value']);
    }

    public function testSubmitValidDataForMultipleAddressForm()
    {
        $formData = ' John Smith 1 <john1@example.com> ;; ; "John Smith 2" <john2@example.com>; john3@example.com';

        $type = new EmailAddressType();
        $form = $this->factory->create($type, null, array('multiple' => true));

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        /** @var array $result */
        $result = $form->getData();
        $this->assertEquals(
            array('John Smith 1 <john1@example.com>', '"John Smith 2" <john2@example.com>', 'john3@example.com'),
            $result
        );

        $view = $form->createView();
        $this->assertEquals(trim($formData), $view->vars['value']);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                array(
                    'multiple' => false
                )
            );

        $type = new EmailAddressType(array());
        $type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $type = new EmailAddressType(array());
        $this->assertEquals('oro_email_email_address', $type->getName());
    }

    public function testGetParent()
    {
        $type = new EmailAddressType(array());
        $this->assertEquals('text', $type->getParent());
    }
}
