<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Form\Type;

use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType;
use PHPUnit\Framework\TestCase;

class PrivilegeCollectionTypeTest extends TestCase
{
    /** @var PrivilegeCollectionType */
    protected $formType;

    protected function setUp(): void
    {
        $this->formType = new PrivilegeCollectionType();
    }

    public function testGetName()
    {
        $this->assertEquals('oro_acl_collection', $this->formType->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('collection', $this->formType->getParent());
    }

    public function testBuildView()
    {
        $view = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $privileges_config = ['permissions' => ['VIEW', 'CREATE']];
        $options = [
            'options' => [
                'privileges_config' => $privileges_config
            ]
        ];
        $this->formType->buildView($view, $form, $options);
        $this->assertAttributeContains($privileges_config, 'vars', $view);
    }
}
