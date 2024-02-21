<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetPriceValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetPriceValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetPriceValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_price_value_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setPriceValueIntent = new SetPriceValue(
            'a_price',
            'ecommerce',
            'en_US',
            new PriceValue(42, 'EUR'),
        );

        $updater->update(
            $product,
            [
                'values' => [
                    'a_price' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => [
                                [
                                    'amount' => '42',
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setPriceValueIntent, $product, 1);
    }

    function it_applies_set_price_value_user_intent_and_add_to_an_existing_price_collection_value(
        ObjectUpdaterInterface $updater,
        ProductInterface $product,
        ValueInterface $formerValue
    ): void {
        $product->getValue('a_price', null, null)->shouldBeCalled()->willReturn($formerValue);
        $formerValue->getData()->willReturn(
            new PriceCollection([
                new ProductPrice('10','USD'),
            ])
        );

        $setPriceValueIntent = new SetPriceValue(
            'a_price',
            null,
            null,
            new PriceValue('42', 'EUR'),
        );

        $updater->update(
            $product,
            [
                'values' => [
                    'a_price' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [
                                [
                                    'amount' => '10',
                                    'currency' => 'USD',
                                ],
                                [
                                    'amount' => '42',
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setPriceValueIntent, $product, 1);
    }

    function it_applies_set_price_value_user_intent_and_update_an_existing_price_collection_value(
        ObjectUpdaterInterface $updater,
        ProductInterface $product,
        ValueInterface $formerValue
    ): void {
        $product->getValue('a_price', null, null)->shouldBeCalled()->willReturn($formerValue);
        $formerValue->getData()->willReturn(
            new PriceCollection([
                new ProductPrice('10','EUR'),
            ])
        );

        $setPriceValueIntent = new SetPriceValue(
            'a_price',
            null,
            null,
            new PriceValue('42', 'EUR'),
        );

        $updater->update(
            $product,
            [
                'values' => [
                    'a_price' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [
                                [
                                    'amount' => '42',
                                    'currency' => 'EUR',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setPriceValueIntent, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
