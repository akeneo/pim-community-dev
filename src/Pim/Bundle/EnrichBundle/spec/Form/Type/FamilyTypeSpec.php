<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\EnrichBundle\Form\Type\FamilyType;
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
            Family::class
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_family');
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
        $resolver->setDefaults(['data_class' => Family::class])->shouldBeCalled();
        $this->configureOptions($resolver);
    }
}
