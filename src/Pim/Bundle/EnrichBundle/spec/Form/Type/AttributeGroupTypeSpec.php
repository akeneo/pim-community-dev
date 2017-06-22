<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroupTranslation;
use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Pim\Bundle\EnrichBundle\Form\Type\AttributeGroupType;
use Pim\Bundle\EnrichBundle\Form\Type\TranslatableFieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeGroupTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeGroup::class);
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_attributegroup');
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('code')->willReturn($builder);
        $builder->add(
            'label',
            TranslatableFieldType::class,
            [
                'field'             => 'label',
                'translation_class' => AttributeGroupTranslation::class,
                'entity_class'      => AttributeGroup::class,
                'property_path'     => 'translations'
            ]
        )->willReturn($builder);

        $builder->add('sort_order', HiddenType::class)->willReturn($builder);
        $builder->addEventSubscriber(new DisableFieldSubscriber('code'))->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => AttributeGroup::class,
            ]
        )->shouldHaveBeenCalled();
    }
}
