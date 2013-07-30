<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Type;

use Symfony\Component\Form\Tests\FormIntegrationTestCase;

use Oro\Bundle\UserBundle\Form\Type\StatusType;
use Oro\Bundle\UserBundle\Entity\Status;

class StatusTypeTest extends FormIntegrationTestCase
{
    public function testBindValidData()
    {
        $formData = array(
            'status' => 'test status',
        );

        $type = new StatusType();
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
