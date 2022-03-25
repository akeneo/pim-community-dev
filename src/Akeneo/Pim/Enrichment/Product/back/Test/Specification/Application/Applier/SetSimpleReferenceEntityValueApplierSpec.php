<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\RemoveMultiReferenceEntityValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetSimpleReferenceEntityValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

class SetSimpleReferenceEntityValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetSimpleReferenceEntityValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_simple_reference_entity_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setSimpleReferenceEntityValue = new SetSimpleReferenceEntityValue(
            'code',
            null,
            null,
            'Akeneo'
        );

        $updater->update(
            $product,
            [
                'values' => [
                    'code' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'Akeneo',
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($setSimpleReferenceEntityValue, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
