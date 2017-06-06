<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber;
use Pim\Component\Catalog\AttributeTypeRegistry;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeTypeSpec extends ObjectBehavior
{
    function let(
        AttributeTypeRegistry $registry,
        AddAttributeTypeRelatedFieldsSubscriber $subscriber,
        FormBuilderInterface $builder
    ) {
        $registry->getAliases()->willReturn(['text', 'number', 'email']);
        $registry->getSortedAliases()->willReturn(['text' => 'text', 'number' => 'number', 'email' => 'email']);

        $this->beConstructedWith(
            $registry,
            $subscriber,
            'Pim\\Bundle\\CatalogBundle\\Entity\\AttributeTranslation',
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            'Pim\Bundle\CatalogBundle\Entity\AttributeGroup'
        );
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_attribute');
    }

    function it_builds_the_attribute_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add(Argument::cetera())->shouldHaveBeenCalled();
    }

    function it_adds_id_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('id', 'hidden')->shouldHaveBeenCalled();
    }

    function it_adds_code_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('code', 'text', ['required' => true])->shouldHaveBeenCalled();
    }

    function it_adds_attribute_type_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder
            ->add(
                'type',
                'choice',
                [
                    'choices'   => ['text' => 'text', 'number' => 'number', 'email' => 'email'],
                    'select2'   => true,
                    'disabled'  => false,
                    'read_only' => true
                ]
            )
            ->shouldHaveBeenCalled();
    }

    function it_gets_attribute_type_choices($builder, $registry)
    {
        $this->buildForm($builder, []);
        $registry->getSortedAliases()->shouldHaveBeenCalled();
    }

    function it_adds_required_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('required', 'switch')->shouldHaveBeenCalled();
    }

    function it_adds_translatable_label_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder
            ->add(
                'label',
                'pim_translatable_field',
                [
                    'field'             => 'label',
                    'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\AttributeTranslation',
                    'entity_class'      => 'Pim\Bundle\CatalogBundle\Entity\Attribute',
                    'property_path'     => 'translations'
                ]
            )->shouldHaveBeenCalled();
    }

    function it_adds_attribute_group_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder
            ->add(
                'group',
                'entity',
                Argument::any()
            )->shouldHaveBeenCalled();
    }

    function it_adds_grid_parameter_fields_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('useableAsGridFilter', 'switch')->shouldHaveBeenCalled();
    }

    function it_sets_the_default_form_data_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\Attribute',
                'cascade_validation' => true,
            ]
        )->shouldBeCalled();
        $this->setDefaultOptions($resolver);
    }

    function it_adds_attribute_type_related_fields_subscriber_to_the_form($builder, $subscriber)
    {
        $this->buildForm($builder, []);
        $subscriber->setFactory(Argument::any())->shouldHaveBeenCalled();
        $builder->addEventSubscriber($subscriber)->shouldHaveBeenCalled();
    }
}
