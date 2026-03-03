<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetMultipleValuesApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetMultipleValuesApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetMultipleValuesApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_multiple_values_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setMultiSelectValue = new SetMultiSelectValue('code', 'ecommerce', 'en_US', ['option1', 'option2']);

        $updater->update(
            $product,
            [
                'values' => [
                    'code' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => ['option1', 'option2'],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setMultiSelectValue, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
