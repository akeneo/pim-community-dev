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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\Parent;

use Akeneo\Platform\Syndication\Application\Common\Selection\Parent\ParentLabelSelection;
use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;
use Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier\SelectionApplierInterface;
use Akeneo\Platform\Syndication\Domain\Query\FindProductModelLabelsInterface;

class ParentLabelSelectionApplier implements SelectionApplierInterface
{
    private FindProductModelLabelsInterface $findProductModelLabels;

    public function __construct(FindProductModelLabelsInterface $findProductModelLabels)
    {
        $this->findProductModelLabels = $findProductModelLabels;
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof ParentLabelSelection
            || !$value instanceof ParentValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Parent selection on this entity');
        }

        $parentCode = $value->getParentCode();
        $parentTranslations = $this->findProductModelLabels->byCodes(
            [$parentCode],
            $selection->getChannel(),
            $selection->getLocale()
        );

        return $parentTranslations[$parentCode] ?? sprintf('[%s]', $parentCode);
    }

    public function supports(SelectionInterface $selection, Target $target, SourceValueInterface $value): bool
    {
        return $selection instanceof ParentLabelSelection
            && $value instanceof ParentValue;
    }
}
