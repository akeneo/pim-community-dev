<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SetAttributeRequirementsTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements');
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_mass_set_attribute_requirements');
    }

    function it_sets_default_options(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements',
            ]
        )->shouldHaveBeenCalled();
    }
}
