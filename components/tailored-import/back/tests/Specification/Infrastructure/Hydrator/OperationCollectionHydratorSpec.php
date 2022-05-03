<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\AttributeTarget;
use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
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
                'type' => 'attribute',
                'attribute_type' => 'pim_catalog_text',
            ],
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
                    'type' => 'attribute',
                    'attribute_type' => 'pim_catalog_text',
                ],
                [
                    ['type' => 'unknown_operation'],
                ],
            ],
        );
    }
}
