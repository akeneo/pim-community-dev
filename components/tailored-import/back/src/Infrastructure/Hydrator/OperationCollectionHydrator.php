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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Hydrator;

use Akeneo\Platform\TailoredImport\Domain\Hydrator\OperationCollectionHydratorInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationCollection;

class OperationCollectionHydrator implements OperationCollectionHydratorInterface
{
    public function hydrate(array $normalizedOperations): OperationCollection
    {
        return OperationCollection::create(
            array_map(
                static fn (array $normalizedOperation) => match ($normalizedOperation['type']) {
                    CleanHTMLTagsOperation::TYPE => new CleanHTMLTagsOperation(),
                    default => throw new \InvalidArgumentException(
                        sprintf('Unsupported "%s" Operation type', $normalizedOperation['type']),
                    ),
                },
                $normalizedOperations,
            ),
        );
    }
}
