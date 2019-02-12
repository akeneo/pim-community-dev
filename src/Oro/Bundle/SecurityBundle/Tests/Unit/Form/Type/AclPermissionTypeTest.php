<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Form\Type;

use Oro\Bundle\SecurityBundle\Form\Type\AclPermissionType;
use Oro\Bundle\SecurityBundle\Model\AclPermission;

class AclPermissionTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var AclPermissionType */
    protected $formType;

    protected function setUp(): void
    {
        $this->formType = new AclPermissionType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $options = [
            'privileges_config' => [
                'field_type' => 'grid'
            ]
        ];
        $builder->expects($this->at(0))->method('add')->with('accessLevel', 'grid', ['required' => false]);
        $builder->expects($this->at(1))->method('add')->with('name', 'hidden', ['required' => false]);
        $this->formType->buildForm($builder, $options);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_acl_permission', $this->formType->getName());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $resolver->expects($this->once())->method('setDefaults')
            ->with(
                [
                    'data_class'        => AclPermission::class,
                    'privileges_config' => []
                ]
            );
        $this->formType->configureOptions($resolver);
    }
}
