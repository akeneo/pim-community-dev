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
use Akeneo\Platform\Syndication\Application\Common\Operation\String\ExtractOperation;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;
use Akeneo\Platform\Syndication\Application\MapValues\OperationApplier\OperationApplierInterface;

class ExtractOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, SourceValueInterface $value): SourceValueInterface
    {
        if (!$operation instanceof ExtractOperation || !($value instanceof StringValue || $value instanceof ParentValue)) {
            throw new \InvalidArgumentException('Cannot apply extract operation on non string value');
        }

        $data = $value instanceof ParentValue ? $value->getParentCode() : $value->getData();
        preg_match(sprintf('/%s/', $operation->getRegexp()), $data, $matches);

        $matchesCount = count($matches);

        if (0 === $matchesCount) {
            return new StringValue('');
        }

        if (1 === $matchesCount) {
            return new StringValue($matches[0]);
        }

        return new StringValue($matches[1]);
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $operation instanceof ExtractOperation && ($value instanceof StringValue || $value instanceof ParentValue);
    }
}
