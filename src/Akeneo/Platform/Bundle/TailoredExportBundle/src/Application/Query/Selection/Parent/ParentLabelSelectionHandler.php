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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\Parent;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionHandlerInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\ParentValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class ParentLabelSelectionHandler implements SelectionHandlerInterface
{
    private GetProductModelLabelsInterface $getProductModelLabels;

    public function __construct(GetProductModelLabelsInterface $getProductModelLabels)
    {
        $this->getProductModelLabels = $getProductModelLabels;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        if (
            !$selection instanceof ParentLabelSelection
            || !$value instanceof ParentValue
        ) {
            throw new \InvalidArgumentException('Cannot apply Parent selection on this entity');
        }

        $parentCode = $value->getData();
        $parentTranslations = $this->getProductModelLabels->byCodesAndLocaleAndScope(
            [$parentCode],
            $selection->getLocale(),
            $selection->getChannel()
        );

        return $parentTranslations[$parentCode] ?? sprintf('[%s]', $parentCode);
    }

    public function supports(SelectionInterface $selection, SourceValueInterface $value): bool
    {
        return $selection instanceof ParentLabelSelection
            && $value instanceof ParentValue;
    }
}
