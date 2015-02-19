<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssociationTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\\Bundle\\CatalogBundle\\Entity\\AttributeRequirement');
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_attribute_requirement');
    }

    function it_build_form_with_keep_non_required_option(FormBuilderInterface $builder)
    {
        $builder->add('required', 'hidden')->shouldBeCalled();

        $this->buildForm($builder, ['keep_non_required']);
    }

    function it_build_form(FormBuilderInterface $builder)
    {
        $builder->add('required', 'checkbox')->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class'        => 'Pim\\Bundle\\CatalogBundle\\Entity\\AttributeRequirement',
                'keep_non_required' => false,
            ]
        )->shouldHaveBeenCalled();
    }
}
