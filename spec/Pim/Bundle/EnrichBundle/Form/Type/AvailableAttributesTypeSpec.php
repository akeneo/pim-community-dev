<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class AvailableAttributesTypeSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        UserContext $userContext,
        TranslatorInterface $translator,
        FormBuilderInterface $builder
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $userContext,
            $translator,
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            'Pim\Bundle\CatalogBundle\Model\AvailableAttributes'
        );
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_available_attributes');
    }

    function it_builds_the_form($builder, $attributeRepository, $userContext)
    {
        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $this->buildForm($builder, ['excluded_attributes' => 'excluded attributes']);
        $builder->add(
            'attributes',
            'pim_async_select',
            [
                'repository' => $attributeRepository,
                'route'      => 'pim_enrich_attribute_rest_index',
                'required'   => false,
                'multiple'   => true,
            ])->shouldHaveBeenCalled();
    }

    function it_sets_the_default_form_data_class(OptionsResolver $resolver)
    {
        $resolver->setNormalizer(Argument::any(), Argument::any())->shouldBeCalled();
        $resolver->setDefaults(
            [
                'data_class'          => 'Pim\Bundle\CatalogBundle\Model\AvailableAttributes',
                'excluded_attributes' => [],
            ]
        )->shouldBeCalled();
        $this->setDefaultOptions($resolver);
    }
}
