<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Pim\Bundle\EnrichBundle\Form\Subscriber\AddAttributeTypeRelatedFieldsSubscriber;
use Pim\Bundle\EnrichBundle\Form\Type\TranslatableFieldType;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Component\Catalog\AttributeTypeRegistry;
use Prophecy\Argument;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

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
            AttributeTranslation::class,
            Attribute::class,
            AttributeGroup::class
        );
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_attribute');
    }

    function it_builds_the_attribute_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add(Argument::cetera())->shouldHaveBeenCalled();
    }

    function it_adds_id_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('id', HiddenType::class)->shouldHaveBeenCalled();
    }

    function it_adds_code_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('code', TextType::class, ['required' => true])->shouldHaveBeenCalled();
    }

    function it_adds_attribute_type_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder
            ->add(
                'type',
                ChoiceType::class,
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
        $builder->add('required', SwitchType::class)->shouldHaveBeenCalled();
    }

    function it_adds_translatable_label_field_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder
            ->add(
                'label',
                TranslatableFieldType::class,
                [
                    'field'             => 'label',
                    'translation_class' => AttributeTranslation::class,
                    'entity_class'      => Attribute::class,
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
                EntityType::class,
                Argument::any()
            )->shouldHaveBeenCalled();
    }

    function it_adds_grid_parameter_fields_to_the_form($builder)
    {
        $this->buildForm($builder, []);
        $builder->add('useableAsGridFilter', SwitchType::class)->shouldHaveBeenCalled();
    }

    function it_sets_the_default_form_data_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'  => Attribute::class,
                'constraints' => new Valid(),
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
