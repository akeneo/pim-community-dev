<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Form\Type\ProductGridFilterChoiceType;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductGridFilterChoiceTypeSpec extends ObjectBehavior
{
    function let(Manager $manager, TranslatedLabelsProviderInterface $attributeProvider)
    {
        $this->beConstructedWith($attributeProvider, $manager, 'product-grid', ['scope', 'locale']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductGridFilterChoiceType::class);
    }

    function it_is_a_form()
    {
        $this->shouldHaveType(AbstractType::class);
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
            'Other' => ['SKU' => 'sku'],
        ]);

        $resolver->setDefaults([
            'choices' => [
                'System' => ['My family' => 'family'],
                'Other' => ['SKU' => 'sku'],
            ],
        ])->shouldBeCalled();

        $this->configureOptions($resolver);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_product_grid_filter_choice');
    }

    function it_has_parent()
    {
        $this->getParent()->shouldReturn(ChoiceType::class);
    }
}
