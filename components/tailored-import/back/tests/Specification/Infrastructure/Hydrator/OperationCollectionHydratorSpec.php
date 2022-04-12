<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use PhpSpec\ObjectBehavior;

class OperationCollectionHydratorSpec extends ObjectBehavior
{
    public function it_hydrates_an_operation_collection(): void
    {
        $expected = OperationCollection::create([
            new CleanHTMLTagsOperation(),
        ]);

        $this->hydrate(
            [
                [
                    'type' => CleanHTMLTagsOperation::TYPE,
                    'value' => true,
                ],
            ],
        )->shouldBeLike($expected);
    }

    public function it_throws_when_operation_is_not_supported(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'hydrate',
            [
                [
                    ['type' => 'unknown_operation'],
                ],
            ],
        );
    }
}
