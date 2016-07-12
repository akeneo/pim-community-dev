<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type;

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
        $this->shouldHaveType('PimEnterprise\Bundle\EnrichBundle\Form\Type\ProductGridFilterChoiceType');
    }

    function it_is_a_form()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Type\ProductGridFilterChoiceType');
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
                'System' => [
                    'family' => 'My family',
                    'permissions' => 'pimee_workflow.product.permission.label',
                ],
                'Other' => ['sku' => 'SKU'],
            ],
        ])->shouldBeCalled();

        $this->configureOptions($resolver);
    }
}
