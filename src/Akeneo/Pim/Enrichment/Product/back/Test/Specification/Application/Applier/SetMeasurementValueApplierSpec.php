<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetMeasurementValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetMeasurementValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetMeasurementValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_measurement_value_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setMeasurementValue = new SetMeasurementValue('code', 'ecommerce', 'en_US', '10', 'g');

        $updater->update(
            $product,
            [
                'values' => [
                    'code' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => [
                                'amount' => '10',
                                'unit' => 'g',
                            ],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setMeasurementValue, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
