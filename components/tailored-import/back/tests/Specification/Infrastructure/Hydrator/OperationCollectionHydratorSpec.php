<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use PhpSpec\ObjectBehavior;

class OperationCollectionHydratorSpec extends ObjectBehavior
{
    public function it_hydrates_an_operation_collection(
        AttributeTarget $target,
    ): void {
        $expected = OperationCollection::create([
            new CleanHTMLTagsOperation(),
        ]);

        $this->hydrateAttribute(
            ['type' => 'pim_catalog_text'],
            [
                [
                    'type' => CleanHTMLTagsOperation::TYPE,
                    'value' => true,
                ],
            ],
        )->shouldBeLike($expected);
    }

    public function it_throws_when_operation_is_not_supported(
        AttributeTarget $target,
    ): void {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'hydrateAttribute',
            [
                ['type' => 'pim_catalog_text'],
                [
                    ['type' => 'unknown_operation'],
                ],
            ],
        );
    }
}
