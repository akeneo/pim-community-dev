<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Form\Type;

use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeIdentityType;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;

class AclPrivilegeIdentityTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var AclPrivilegeIdentityType */
    protected $formType;

    protected function setUp(): void
    {
        $this->formType = new AclPrivilegeIdentityType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->at(0))->method('add')->with('id', 'hidden', ['required' => true]);
        $builder->expects($this->at(1))->method('add')->with('name', 'oro_acl_label', ['required' => false]);
        $this->formType->buildForm($builder, []);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_acl_privilege_identity', $this->formType->getName());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $resolver->expects($this->once())->method('setDefaults')
            ->with(
                [
                    'data_class' => AclPrivilegeIdentity::class
                ]
            );
        $this->formType->configureOptions($resolver);
    }
}
