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
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;

class ReferenceEntityReplacementOperationApplier implements OperationApplierInterface
{
    public function applyOperation(
        OperationInterface $operation,
        SourceValueInterface $value
    ): SourceValueInterface {
        if (
            !$operation instanceof ReplacementOperation
            || !$value instanceof ReferenceEntityValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity replacement operation');
        }

        $recordCode = $value->getRecordCode();

        if ($operation->hasMappedValue($recordCode)) {
            $mappedValue = $operation->getMappedValue($recordCode);

            return new StringValue($mappedValue);
        }

        return $value;
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $value instanceof ReferenceEntityValue && $operation instanceof ReplacementOperation;
    }
}
