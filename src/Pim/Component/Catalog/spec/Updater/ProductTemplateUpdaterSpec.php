<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTemplateUpdaterSpec extends ObjectBehavior
{
    function let(PropertySetterInterface $propertySetter, NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($propertySetter, $normalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\ProductTemplateUpdater');
    }

    function it_is_a_product_template_updater()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface');
    }

    function it_updates_products_with_variant_group_template_values_using_product_updater(
        $propertySetter,
        $normalizer,
        ProductTemplateInterface $template,
        ProductInterface $product,
        ProductValueCollection $values
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
                        ['amount' => 10, 'currency' => 'EUR'],
                        ['amount' => 20, 'currency' => 'USD']
                    ]
                ]
            ],
            'image' => [
                [
                    'locale' => null,
                    'scope'  => 'mobile',
                    'data'   => '/uploads/image.jpg'
                ]
            ]
        ];

        $template->getValues()->willReturn($values);
        $normalizer->normalize($values, 'standard')->willReturn($updates);

        $propertySetter
            ->setData($product, 'description', 'Foo', ['locale' => 'en_US', 'scope' => 'ecommerce'])
            ->shouldBeCalled();

        $propertySetter
            ->setData($product, 'description', 'Bar', ['locale' => 'en_US', 'scope' => 'mobile'])
            ->shouldBeCalled();

        $propertySetter
            ->setData($product, 'color', 'red', ['locale' => null, 'scope' => null])
            ->shouldBeCalled();

        $propertySetter
            ->setData(
                $product,
                'price',
                [['amount' => 10, 'currency' => 'EUR'], ['amount' => 20, 'currency' => 'USD']],
                ['locale' => 'fr_FR', 'scope' => null]
            )
            ->shouldBeCalled();

        $propertySetter
            ->setData($product, 'image', '/uploads/image.jpg', ['locale' => null, 'scope' => 'mobile'])
            ->shouldBeCalled();

        $this->update($template, [$product]);
    }
}
