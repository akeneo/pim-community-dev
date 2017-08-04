<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\EnrichBundle\Form\Type\LightEntityType;
use Pim\Component\Enrich\Model\AvailableAttributes;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Prophecy\Argument;
use Symfony\Component\Form\Test\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class AvailableAttributesTypeSpec extends ObjectBehavior
{
    function let(
        TranslatedLabelsProviderInterface $attributeRepository,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $translator,
            Attribute::class,
            AvailableAttributes::class
        );
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_available_attributes');
    }

    function it_builds_the_form(FormBuilderInterface $builder, $attributeRepository)
    {
        $this->buildForm($builder, ['excluded_attributes' => 'excluded attributes']);
        $builder->add(
            'attributes',
            LightEntityType::class,
            [
                'repository'         => $attributeRepository,
                'repository_options' => [
                    'excluded_attribute_ids' => 'excluded attributes',
                ],
                'multiple'           => true,
                'expanded'           => false,
            ])->shouldHaveBeenCalled();
    }

    function it_sets_the_default_form_data_class(OptionsResolver $resolver)
    {
        $resolver->setNormalizer(Argument::any(), Argument::any())->shouldBeCalled();
        $resolver->setDefaults(
            [
                'data_class'          => AvailableAttributes::class,
                'excluded_attributes' => [],
            ]
        )->shouldBeCalled();
        $this->configureOptions($resolver);
    }
}
