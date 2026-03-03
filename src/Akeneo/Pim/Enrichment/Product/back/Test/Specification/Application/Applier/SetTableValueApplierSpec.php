<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTableValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetTableValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetTableValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetTableValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_table_value_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setTable = new SetTableValue(
            'nutrition',
            'ecommerce',
            'en_US',
            [
                ['ingredient' => 'salt'],
                ['ingredient' => 'egg', 'quantity' => 2],
            ]
        );

        $updater->update(
            $product,
            [
                'values' => [
                    'nutrition' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => [
                                ['ingredient' => 'salt'],
                                ['ingredient' => 'egg', 'quantity' => 2],
                            ],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setTable, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
