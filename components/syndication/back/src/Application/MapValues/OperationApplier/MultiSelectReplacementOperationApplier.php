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

namespace Akeneo\Platform\Syndication\Application\MapValues\OperationApplier;

use Akeneo\Platform\Syndication\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\Syndication\Application\Common\Operation\ReplacementOperation;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MultiSelectValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;

class MultiSelectReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(
        OperationInterface $operation,
        SourceValueInterface $value
    ): SourceValueInterface {
        if (
            !$operation instanceof ReplacementOperation
            || !$value instanceof MultiSelectValue
        ) {
            throw new \InvalidArgumentException('Cannot apply multi select option replacement operation');
        }

        $mappedOptionValues = array_intersect_key($operation->getMapping(), array_flip($value->getOptionCodes()));

        return new MultiSelectValue($value->getOptionCodes(), $mappedOptionValues);
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $value instanceof MultiSelectValue && $operation instanceof ReplacementOperation;
    }
}
