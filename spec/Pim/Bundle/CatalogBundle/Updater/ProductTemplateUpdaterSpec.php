<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;

class ProductTemplateUpdaterSpec extends ObjectBehavior
{
    function let(PropertySetterInterface $productFieldUpdater)
    {
        $this->beConstructedWith($productFieldUpdater);
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
        $productFieldUpdater,
        ProductTemplateInterface $template,
        ProductInterface $product
    ) {
        $updates = [
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'ecommerce',
                    'data'   => 'Foo'
                ],
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'data'   => 'Bar'
                ]
            ],
            'color' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'red'
                ]
            ],
            'price' => [
                [
                    'locale' => 'fr_FR',
                    'scope'  => null,
                    'data'   => [
                        ['data' => 10, 'currency' => 'EUR'],
                        ['data' => 20, 'currency' => 'USD']
                    ]
                ]
            ],
            'image' => [
                [
                    'locale' => null,
                    'scope'  => 'mobile',
                    'data'   => [
                        'filePath' => '/uploads/image.jpg',
                        'originalFilename' => 'Image.jpg'
                    ]
                ]
            ]
        ];

        $template->getValuesData()->willReturn($updates);

        $productFieldUpdater->setData($product, 'description', 'Foo', ['locale' => 'en_US', 'scope' => 'ecommerce'])->shouldBeCalled();
        $productFieldUpdater->setData($product, 'description', 'Bar', ['locale' => 'en_US', 'scope' => 'mobile'])->shouldBeCalled();
        $productFieldUpdater->setData($product, 'color', 'red', ['locale' => null, 'scope' => null])->shouldBeCalled();
        $productFieldUpdater
            ->setData(
                $product,
                'price',
                [['data' => 10, 'currency' => 'EUR'], ['data' => 20, 'currency' => 'USD']],
                ['locale' => 'fr_FR', 'scope' => null]
            )
            ->shouldBeCalled();
        $productFieldUpdater
            ->setData(
                $product,
                'image',
                ['filePath' => '/uploads/image.jpg', 'originalFilename' => 'Image.jpg'],
                ['locale' => null, 'scope' => 'mobile']
            )
            ->shouldBeCalled();

        $this->update($template, [$product]);
    }
}
