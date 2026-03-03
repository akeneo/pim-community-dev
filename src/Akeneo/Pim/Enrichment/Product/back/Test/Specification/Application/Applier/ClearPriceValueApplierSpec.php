<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPrice;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\ClearPriceValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class ClearPriceValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater): void
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ClearPriceValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_clear_price_value_user_intent(
        ObjectUpdaterInterface $updater,
        ProductInterface $product,
        ValueInterface $formerValue,
    ): void {
        $product->getValue('a_price', null, null)->shouldBeCalled()->willReturn($formerValue);
        $formerValue->getData()->willReturn(
            new PriceCollection([
                new ProductPrice('10', 'USD'),
                new ProductPrice('42', 'EUR'),
            ]),
        );

        $clearPriceValueIntent = new ClearPriceValue('a_price', null, null, 'USD');

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
            ],
        )->shouldBeCalledOnce();

        $this->apply($clearPriceValueIntent, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
