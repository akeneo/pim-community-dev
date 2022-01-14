<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\Syndication\Application\MapValues\OperationApplier;

use Akeneo\Platform\Syndication\Application\Common\Operation\DefaultValueOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;
use PhpSpec\ObjectBehavior;

class ReferenceEntityCollectionReplacementOperationApplierSpec extends ObjectBehavior
{
    public function it_supports_replacement_operation_and_reference_entity_collection_value()
    {
        $operation = new ReplacementOperation([
            'record_code_1' => 'record_code_1',
            'record_code_2' => 'record_code_2',
            'record_code_3' => 'record_code_3',
        ]);

        $value = new ReferenceEntityCollectionValue(['record_code_1', 'record_code_3']);

        $this->supports($operation, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }

    public function it_applies_replacement_operation()
    {
        $operation = new ReplacementOperation([
            'record_code_1' => 'record_code_1',
            'record_code_2' => 'record_code_2',
            'record_code_3' => 'record_code_3',
        ]);

        $value = new ReferenceEntityCollectionValue(['record_code_1', 'record_code_3']);

        $this->applyOperation($operation, $value)->shouldBeLike(
            new ReferenceEntityCollectionValue(
                [
                    'record_code_1',
                    'record_code_3'
                ],
                [
                    'record_code_1' => 'record_code_1',
                    'record_code_3' => 'record_code_3',
                ]
            )
        );
    }

    public function it_does_nothing_when_value_is_not_mapped()
    {
        $operation = new ReplacementOperation([]);

        $value = new ReferenceEntityCollectionValue(['record_code_1', 'record_code_3']);

        $this->applyOperation($operation, $value)->shouldBeLike($value);
    }

    public function it_throws_when_operation_or_value_is_invalid()
    {
        $notSupportedSelection = new DefaultValueOperation('n/a');
        $notSupportedValue = new StringValue('name');

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('applyOperation', [$notSupportedSelection, $notSupportedValue]);
    }
}
