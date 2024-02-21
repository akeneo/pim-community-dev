<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetPriceCollectionValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetPriceCollectionValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetPriceCollectionValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_price_collection_value_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setPriceValueIntent = new SetPriceCollectionValue(
            'msrp',
            'ecommerce',
            'en_US',
            [
                new PriceValue(42, 'EUR'),
                new PriceValue('45', 'USD'),
            ]
        );

        $updater->update(
            $product,
            [
                'values' => [
                    'msrp' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => [
                                [
                                    'amount' => '42',
                                    'currency' => 'EUR',
                                ],
                                [
                                    'amount' => '45',
                                    'currency' => 'USD',
                                ]
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
