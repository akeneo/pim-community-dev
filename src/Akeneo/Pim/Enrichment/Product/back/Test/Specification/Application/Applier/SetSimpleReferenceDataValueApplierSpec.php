<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\SetSimpleReferenceDataValueApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetSimpleReferenceDataValueApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $updater)
    {
        $this->beConstructedWith($updater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SetSimpleReferenceDataValueApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_applies_set_simple_reference_data_user_intent(ObjectUpdaterInterface $updater): void
    {
        $product = new Product();
        $setSimpleReferenceDataValue = new SetSimpleReferenceDataValue(
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

        $this->apply($setSimpleReferenceDataValue, $product, 1);
    }

    function it_throws_an_exception_when_user_intent_is_not_supported(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('apply', [new SetEnabled(true), new Product(), 1]);
    }
}
