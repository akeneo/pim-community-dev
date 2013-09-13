<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Type;

use Oro\Bundle\UserBundle\Form\Type\ChangePasswordType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class ChangePasswordTypeTest extends FormIntegrationTestCase
{
    public function setUp()
    {

    }

    public function testBuildForm()
    {
        $formData = array(
            'status' => 'test status',
        );

        $type = new ChangePasswordType();
        $form = $this->factory->create($type);

        $status = new Status();
        $status->setStatus($formData['status']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($status, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
