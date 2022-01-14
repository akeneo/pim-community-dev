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

namespace Akeneo\Platform\Syndication\Application\MapValues\OperationApplier\String;

use Akeneo\Platform\Syndication\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\Syndication\Application\Common\Operation\String\SplitOperation;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;
use Akeneo\Platform\Syndication\Application\MapValues\OperationApplier\OperationApplierInterface;

class SplitOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, SourceValueInterface $value): SourceValueInterface
    {
        if (!$operation instanceof SplitOperation || !($value instanceof StringValue || $value instanceof ParentValue)) {
            throw new \InvalidArgumentException('Cannot apply split operation on non string value');
        }

        $data = $value instanceof ParentValue ? $value->getParentCode() : $value->getData();

        if (!$operation->getSeparator()) {
            return new StringCollectionValue([$data]);
        }

        $separator = '\n' === $operation->getSeparator() ? PHP_EOL : $operation->getSeparator();

        $stringCollection = array_map(fn ($value) => trim($value), explode($separator, $data));

        return new StringCollectionValue($stringCollection);
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $operation instanceof SplitOperation && ($value instanceof StringValue || $value instanceof ParentValue);
    }
}
