<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Extension;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Type\AttributeType;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;

class AttributeTypeExtensionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\Form\Extension\AttributeTypeExtension');
    }
    
    function it_is_form()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractTypeExtension');
    }
    
    function it_builds_a_form(FormBuilderInterface $builder)
    {
        $builder->add('isReadOnly', SwitchType::class, [
            'required' => false,
            'property_path' => 'properties[is_read_only]',
        ])->shouldBeCalled();
        
        $this->buildForm($builder);
    }

    function it_extends_form_type()
    {
        $this->getExtendedType()->shouldReturn(AttributeType::class);
    }
}
