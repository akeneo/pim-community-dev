<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Application\Applier\RemoveMultiReferenceEntityValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RemoveMultiReferenceEntityValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveMultiReferenceEntityValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_remove_multi_reference_entity_user_intent(
        ObjectUpdaterInterface $updater,
        ProductInterface $product,
        ValueInterface $formerRecordCodes
    ) {
        $removeMultiReferenceEntityValue = new RemoveMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Akeneo', 'Ziggy']
        );

        $product->getValue('code', null, null)->shouldBeCalled()->willReturn($formerRecordCodes);
        $formerRecordCodes->getData()->willReturn(
            [
                'Akeneo',
                'AnotherAkeneo',
                'Ziggy',
                'AnotherZiggy',
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
                            'data' => ['AnotherAkeneo', 'AnotherZiggy'],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($removeMultiReferenceEntityValue, $product, 1);
    }

    function it_removes_the_last_records(
        ObjectUpdaterInterface $updater,
        ProductInterface $product,
        ValueInterface $formerRecordCodes
    ) {
        $removeMultiReferenceEntityValue = new RemoveMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Akeneo']
        );

        $product->getValue('code', null, null)->shouldBeCalled()->willReturn($formerRecordCodes);
        $formerRecordCodes->getData()->willReturn(['Akeneo']);

        $updater->update(
            $product,
            [
                'values' => [
                    'code' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => [],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalledOnce();

        $this->apply($removeMultiReferenceEntityValue, $product, 1);
    }

    function it_does_nothing_when_product_has_no_record_to_remove(
        ObjectUpdaterInterface $updater,
        ProductInterface $product
    ) {
        $removeMultiReferenceEntityValue = new RemoveMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Akeneo', 'Ziggy']
        );

        $product->getValue('code', null, null)->shouldBeCalledOnce()->willReturn(null);
        $updater->update(Argument::any())->shouldNotBeCalled();

        $this->apply($removeMultiReferenceEntityValue, $product, 1);
    }

    function it_does_nothing_when_product_does_not_have_the_record_to_remove(
        ObjectUpdaterInterface $updater,
        ProductInterface $product,
        ValueInterface $formerRecordCodes
    ) {
        $removeMultiReferenceEntityValue = new RemoveMultiReferenceEntityValue(
            'code',
            null,
            null,
            ['Ziggy']
        );

        $product->getValue('code', null, null)->shouldBeCalled()->willReturn($formerRecordCodes);
        $formerRecordCodes->getData()->willReturn(['Akeneo']);
        $updater->update(Argument::any())->shouldNotBeCalled();

        $this->apply($removeMultiReferenceEntityValue, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported()
    {
        $product = new Product();
        $setEnabledUserIntent = new SetEnabled(true);
        $this->shouldThrow(\InvalidArgumentException::class)->during('apply', [$setEnabledUserIntent, $product, 1]);
    }
}
