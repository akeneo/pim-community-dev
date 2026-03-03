<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetDateValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetDateValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetDateValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_date_value_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setDateValue = new SetDateValue('code', 'ecommerce', 'en_US', new \DateTime("2022-03-04T09:35:24+00:00"));

        $updater->update(
            $product,
            [
                'values' => [
                    'code' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => '2022-03-04',
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setDateValue, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
