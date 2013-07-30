<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Type;

use Symfony\Component\Form\Tests\FormIntegrationTestCase;

use Oro\Bundle\UserBundle\Form\Type\EmailType;
use Oro\Bundle\UserBundle\Entity\Email;

class EmailTypeTest extends FormIntegrationTestCase
{
    public function testBindValidData()
    {
        $formData = array(
            'email' => 'test@example.com',
        );

        $type = new EmailType();
        $form = $this->factory->create($type);

        $email = new Email();
        $email->setEmail($formData['email']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($email, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
