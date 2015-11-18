<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Form\Type;

use Oro\Bundle\UserBundle\Form\Type\AclRoleType;

class AclRoleTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var AclRoleType */
    protected $formType;

    protected function setUp()
    {
        $this->formType = new AclRoleType(['field' => 'field_config']);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->at(0))->method('add')
            ->with('label', 'text', ['required' => true, 'label' => 'Role']);
        $builder->expects($this->at(1))->method('add')
            ->with(
                'field',
                $this->isInstanceOf('Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType'),
                $this->contains(['privileges_config' => 'field_config'])
            );
        $this->formType->buildForm($builder, []);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_user_role_form', $this->formType->getName());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $resolver->expects($this->once())->method('setDefaults')
            ->with(
                [
                    'data_class' => 'Oro\Bundle\UserBundle\Entity\Role',
                    'intention'  => 'role'
                ]
            );
        $this->formType->configureOptions($resolver);
    }
}
