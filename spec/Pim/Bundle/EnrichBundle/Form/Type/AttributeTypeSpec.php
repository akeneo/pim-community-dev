<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Manager\AttributeManagerInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber;

class AttributeTypeSpec extends ObjectBehavior
{
    function let(
        AttributeManagerInterface $manager,
        AddAttributeTypeRelatedFieldsSubscriber $subscriber,
        FormBuilderInterface $builder
    ) {
        $manager->getAttributeTypes()->willReturn(['text', 'number', 'email']);

        $this->beConstructedWith('Pim\Bundle\CatalogBundle\Entity\Attribute', $manager, $subscriber);
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
                'attributeType',
                'choice',
                [
                    'choices' => ['text', 'number', 'email'],
                    'select2' => true
                ]
            )
            ->shouldHaveBeenCalled();
    }

    function it_gets_attribute_type_choices_from_attribute_manager($builder, $manager)
    {
        $this->buildForm($builder, []);
        $manager->getAttributeTypes()->shouldHaveBeenCalled();
    }

    function it_adds_unique_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('unique', 'choice', ['choices' => ['No', 'Yes']])->shouldHaveBeenCalled();
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
                [
                    'class'       => 'Pim\Bundle\CatalogBundle\Entity\AttributeGroup',
                    'required'    => false,
                    'multiple'    => false,
                    'empty_value' => AttributeGroup::DEFAULT_GROUP_CODE,
                    'select2'     => true
                ]
            )->shouldHaveBeenCalled();
    }

    function it_adds_grid_parameter_fields_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('useableAsGridColumn', 'switch')->shouldHaveBeenCalled();
        $builder->add('useableAsGridFilter', 'switch')->shouldHaveBeenCalled();
    }

    function it_sets_the_default_form_data_class(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Pim\Bundle\CatalogBundle\Entity\Attribute'])->shouldBeCalled();
        $this->setDefaultOptions($resolver);
    }

    function it_adds_attribute_type_related_fields_subscriber_to_the_form($builder, $subscriber)
    {
        $this->buildForm($builder, []);
        $subscriber->setFactory(Argument::any())->shouldHaveBeenCalled();
        $builder->addEventSubscriber($subscriber)->shouldHaveBeenCalled();
    }
}
