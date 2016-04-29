<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface;
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
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            'Pim\Component\Enrich\Model\AvailableAttributes'
        );
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_available_attributes');
    }

    function it_builds_the_form(FormBuilderInterface $builder, $attributeRepository)
    {
        $this->buildForm($builder, ['excluded_attributes' => 'excluded attributes']);
        $builder->add(
            'attributes',
            'light_entity',
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
                'data_class'          => 'Pim\Component\Enrich\Model\AvailableAttributes',
                'excluded_attributes' => [],
            ]
        )->shouldBeCalled();
        $this->setDefaultOptions($resolver);
    }
}
