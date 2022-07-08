<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetSingleValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetSingleValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetSingleValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_a_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setText = new SetTextValue('code', 'ecommerce', 'en_US', 'foo');

        $updater->update(
            $product,
            [
                'values' => [
                    'code' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => 'foo',
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setText, $product, 1);
    }

    function it_applies_an_identifier_value_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setText = new SetIdentifierValue('sku', 'foo');

        $updater->update(
            $product,
            [
                'values' => [
                    'sku' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'foo',
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setText, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
