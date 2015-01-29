<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;

class ProductTemplateUpdaterSpec extends ObjectBehavior
{
    function let(ProductUpdaterInterface $productUpdater)
    {
        $this->beConstructedWith($productUpdater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdater');
    }

    function it_is_a_product_template_updater()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdaterInterface');
    }

    function it_updates_products_with_variant_group_template_values_using_product_updater(
        $productUpdater,
        ProductTemplateInterface $template,
        ProductInterface $product
    ) {
        $updates = [
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'value'  => 'Foo'
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'Bar'
                ]
            ],
            'color' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'value'  => 'red'
                ]
            ],
            'price' => [
                [
                    'locale' => 'fr_FR',
                    'scope' => null,
                    'value' => [
                        ['data' => 10, 'currency' => 'EUR'],
                        ['data' => 20, 'currency' => 'USD']
                    ]
                ]
            ],
            'image' => [
                [
                    'locale' => null,
                    'scope' => 'mobile',
                    'value' => [
                        'filePath' => '/uploads/image.jpg',
                        'originalFilename' => 'Image.jpg'
                    ]
                ]
            ]
        ];

        $template->getValuesData()->willReturn($updates);

        $productUpdater->setValue([$product], 'description', 'Foo', 'en_US', 'ecommerce')->shouldBeCalled();
        $productUpdater->setValue([$product], 'description', 'Bar', 'en_US', 'mobile')->shouldBeCalled();
        $productUpdater->setValue([$product], 'color', 'red', null, null)->shouldBeCalled();
        $productUpdater
            ->setValue(
                [$product],
                'price',
                [['data' => 10, 'currency' => 'EUR'], ['data' => 20, 'currency' => 'USD']],
                'fr_FR',
                null
            )
            ->shouldBeCalled();
        $productUpdater
            ->setValue(
                [$product],
                'image',
                ['filePath' => '/uploads/image.jpg', 'originalFilename' => 'Image.jpg'],
                null,
                'mobile'
            )
            ->shouldBeCalled();

        $this->update($template, [$product]);
    }
}
