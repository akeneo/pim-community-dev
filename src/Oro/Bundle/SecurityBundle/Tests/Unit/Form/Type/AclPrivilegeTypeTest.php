<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Form\Type;

use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeIdentityType;
use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeType;
use Oro\Bundle\SecurityBundle\Form\Type\PermissionCollectionType;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;

class AclPrivilegeTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var AclPrivilegeType */
    protected $formType;

    protected function setUp(): void
    {
        $this->formType = new AclPrivilegeType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->at(0))->method('add')->with(
            'identity',
            $this->isInstanceOf(AclPrivilegeIdentityType::class),
            ['required' => false]
        );
        $options = [
            'privileges_config' => [
                'field_type' => 'grid'
            ]
        ];
        $builder->expects($this->at(1))->method('add')->with(
            'permissions',
            $this->isInstanceOf(PermissionCollectionType::class),
            $this->contains($options)
        );
        $this->formType->buildForm($builder, $options);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_acl_privilege', $this->formType->getName());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $resolver->expects($this->once())->method('setDefaults')
            ->with(
                [
                    'privileges_config' => [],
                    'data_class'        => AclPrivilege::class,
                ]
            );
        $this->formType->configureOptions($resolver);
    }

    public function testBuildView()
    {
        $view = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $privileges_config = ["test"];
        $options = [
            'privileges_config' => $privileges_config
        ];
        $this->formType->buildView($view, $form, $options);
        $this->assertAttributeContains($privileges_config, 'vars', $view);
    }
}
