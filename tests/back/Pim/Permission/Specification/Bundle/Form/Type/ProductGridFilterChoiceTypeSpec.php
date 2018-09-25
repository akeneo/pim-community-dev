<?php

// TODO To delete

namespace Specification\Akeneo\Pim\Permission\Bundle\Form\Type;

use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Permission\Bundle\Form\Type\ProductGridFilterChoiceType;
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
        $this->shouldHaveType(ProductGridFilterChoiceType::class);
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
                    'My family' => 'family',
                    'pim_common.permissions' => 'permissions',
                ],
                'Other' => ['sku' => 'SKU'],
            ],
        ])->shouldBeCalled();

        $this->configureOptions($resolver);
    }
}
