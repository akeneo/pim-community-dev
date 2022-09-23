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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\ReferenceEntity;

use Akeneo\Platform\Syndication\Application\Common\Selection\ReferenceEntity\ReferenceEntityCodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ReferenceEntityValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;

class ReferenceEntityCodeSelectionApplier implements SelectionApplierInterface
{
    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof ReferenceEntityCodeSelection
            || !$value instanceof ReferenceEntityValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Reference Entity selection on this entity');
        }

        return $value->getRecordCode();
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof ReferenceEntityCodeSelection
            && $value instanceof ReferenceEntityValue;
    }
}
