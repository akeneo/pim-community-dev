<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FamilyTypeSpec extends ObjectBehavior
{
    function let(
        FormBuilderInterface $builder
    ) {
        $builder->addEventSubscriber(Argument::any())->willReturn($builder);
        $builder->add(Argument::cetera())->willReturn($builder);

        $this->beConstructedWith(
            'Pim\Bundle\CatalogBundle\Entity\Family'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Type\FamilyType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_family');
    }

    function it_builds_the_family_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add(Argument::cetera())->shouldHaveBeenCalled();
    }

    function it_adds_code_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('code')->shouldHaveBeenCalled();
    }

    function it_sets_the_default_form_data_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Pim\Bundle\CatalogBundle\Entity\Family'])->shouldBeCalled();
        $this->setDefaultOptions($resolver);
    }
}
