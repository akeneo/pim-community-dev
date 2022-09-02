<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\ClearValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class ClearValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater): void
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ClearValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_clear_value_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $clearValue = new ClearValue('code', 'ecommerce', 'en_US');

        $updater->update(
            $product,
            [
                'values' => [
                    'code' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => null,
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($clearValue, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
