<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AssociationTypeTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\CatalogBundle\Entity\AssociationType');
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_association_type');
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('code')->shouldBeCalled();
        $builder->addEventSubscriber(new DisableFieldSubscriber('code'))->shouldBeCalled();

        $builder->add(
            'label',
            'pim_translatable_field',
            [
                'field'             => 'label',
                'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\AssociationTypeTranslation',
                'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\AssociationType',
                'property_path'     => 'translations'
            ]
        )->shouldBeCalled();

        $this->buildForm($builder, []);

    }

    function it_sets_default_options(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\AssociationType',
            ]
        )->shouldHaveBeenCalled();
    }
}
