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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\PropertySelector;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Platform\TailoredExport\Domain\SelectionTypes;

class ParentSelector implements PropertySelectorInterface
{
    private GetProductModelLabelsInterface $getProductModelLabels;

    public function __construct(GetProductModelLabelsInterface $getProductModelLabels)
    {
        $this->getProductModelLabels = $getProductModelLabels;
    }

    public function applySelection(array $selectionConfiguration, $entity): string
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            throw new \LogicException('Cannot apply parent selection on this entity');
        }

        $parent = $entity->getParent();
        if (null === $parent) {
            return '';
        }

        $parentCode = $parent->getCode();
        switch ($selectionConfiguration['type']) {
            case SelectionTypes::CODE:
                return $parentCode;
            case SelectionTypes::LABEL:
                $parentTranslations = $this->getProductModelLabels->byCodesAndLocaleAndScope(
                    [$parentCode],
                    $selectionConfiguration['locale'],
                    $selectionConfiguration['channel']
                );

                return $parentTranslations[$parentCode] ?? sprintf('[%s]', $parentCode);
            default:
                throw new \LogicException(sprintf('Selection type "%s" is not supported', $selectionConfiguration['type']));
        }
    }

    public function supports(array $selectionConfiguration, string $propertyName): bool
    {
        return in_array($selectionConfiguration['type'], [SelectionTypes::LABEL, SelectionTypes::CODE])
            && 'parent' === $propertyName;
    }
}
