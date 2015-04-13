<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeAsLabelSubscriber;
use Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeRequirementsSubscriber;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFamilyFieldsSubscriber;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FamilyTypeSpec extends ObjectBehavior
{
    function let(
        AddAttributeRequirementsSubscriber $requireSubscriber,
        DisableFamilyFieldsSubscriber $disableSubscriber,
        AddAttributeAsLabelSubscriber $attributeAsLabelSubscriber,
        FormBuilderInterface $builder
    ) {
        $builder->addEventSubscriber(Argument::any())->willReturn($builder);
        $builder->add(Argument::cetera())->willReturn($builder);

        $this->beConstructedWith(
            $requireSubscriber,
            $disableSubscriber,
            $attributeAsLabelSubscriber,
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
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
        $builder->addEventSubscriber(Argument::cetera())->shouldHaveBeenCalled();
    }

    function it_adds_code_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('code')->shouldHaveBeenCalled();
    }

    function it_adds_label_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('label', 'pim_translatable_field', Argument::any())->shouldHaveBeenCalled();
    }

    function it_adds_attribute_requirements_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('indexedAttributeRequirements', 'collection', ['type' => 'pim_enrich_attribute_requirement'])->shouldHaveBeenCalled();
    }

    function it_sets_the_default_form_data_class(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Pim\Bundle\CatalogBundle\Entity\Family'])->shouldBeCalled();
        $this->setDefaultOptions($resolver);
    }
}
