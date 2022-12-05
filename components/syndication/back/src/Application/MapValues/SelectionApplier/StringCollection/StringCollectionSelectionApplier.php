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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\StringCollection;

use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ReferenceEntityCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringCollectionValue;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class StringCollectionSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): array
    {
        if (!($value instanceof StringCollectionValue || $value instanceof ReferenceEntityCollectionValue)) {
            throw new \InvalidArgumentException('Cannot apply String collection selection on this entity');
        }

        if ($value instanceof ReferenceEntityCollectionValue) {
            return $value->getRecordCodes();
        }

        return $value->getData();
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $value instanceof StringCollectionValue || $value instanceof ReferenceEntityCollectionValue;
    }
}
