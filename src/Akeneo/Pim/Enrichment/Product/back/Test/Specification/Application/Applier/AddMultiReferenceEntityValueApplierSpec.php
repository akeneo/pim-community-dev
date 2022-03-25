<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\AddMultiReferenceEntityValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AddMultiReferenceEntityValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AddMultiReferenceEntityValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_add_multi_reference_entity_user_intent(
        ObjectUpdaterInterface $updater,
        ProductInterface $product,
        ValueInterface $formerRecordCodes
    ) {
        $addMultiReferenceEntityValue = new AddMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['AnotherAkeneo', 'AnotherZiggy']
        );

        $product->getValue('code', null, null)->shouldBeCalled()->willReturn($formerRecordCodes);
        $formerRecordCodes->getData()->willReturn(
            [
                'Akeneo',
                'Ziggy',
            ]
        );

        $updater->update(
            $product,
            [
                'values' => [
                    'code' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => ['Akeneo', 'Ziggy', 'AnotherAkeneo', 'AnotherZiggy'],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($addMultiReferenceEntityValue, $product, 1);
    }

    function it_does_not_update_the_product_when_there_is_nothing_to_add(
        ObjectUpdaterInterface $updater,
        ProductInterface $product,
        ValueInterface $formerRecordCodes
    ) {
        $addMultiReferenceEntityValue = new AddMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Ziggy', 'Akeneo']
        );

        $product->getValue('code', null, null)->shouldBeCalled()->willReturn($formerRecordCodes);
        $formerRecordCodes->getData()->willReturn(['Akeneo', 'Ziggy']);

        $updater->update(Argument::any())->shouldNotBeCalled();

        $this->apply($addMultiReferenceEntityValue, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported()
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
