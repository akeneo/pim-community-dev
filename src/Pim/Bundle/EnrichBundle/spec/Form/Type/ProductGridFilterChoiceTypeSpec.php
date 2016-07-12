<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductGridFilterChoiceTypeSpec extends ObjectBehavior
{
    function let(Manager $manager, TranslatedLabelsProviderInterface $attributeProvider)
    {
        $this->beConstructedWith($attributeProvider, $manager, 'product-grid', ['scope', 'locale']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Type\ProductGridFilterChoiceType');
    }

    function it_is_a_form()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    function it_has_options(
        $manager,
        $attributeProvider,
        OptionsResolver $resolver,
        DatagridConfiguration $configuration
    ) {
        $manager->getConfigurationForGrid('product-grid')->willReturn($configuration);
        $configuration->offsetGetByPath('[filters][columns]')->willReturn([
            'family' => [
                'label' => 'My family',
            ],
            'locale' => [
                'label' => 'Locale',
            ]
        ]);
        
        $attributeProvider->findTranslatedLabels(['useable_as_grid_filter' => true])->willReturn([
            'Other' => ['sku' => 'SKU'],
        ]);
        
        $resolver->setDefaults([
            'choices' => [
                'System' => ['family' => 'My family'],
                'Other' => ['sku' => 'SKU'],
            ],
        ])->shouldBeCalled();

        $this->configureOptions($resolver);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_product_grid_filter_choice');
    }

    function it_has_parent()
    {
        $this->getParent()->shouldReturn('choice');
    }
}
