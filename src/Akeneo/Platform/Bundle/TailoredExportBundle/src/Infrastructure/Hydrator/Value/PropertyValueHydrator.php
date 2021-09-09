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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\CategoriesValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FamilyValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;

class PropertyValueHydrator
{
    public function hydrate(string $propertyName, ProductInterface $product): SourceValueInterface
    {
        switch ($propertyName) {
            case 'categories':
                return new CategoriesValue($product->getCategoryCodes());
            case 'enabled':
                return new EnabledValue($product->isEnabled());
            case 'family':
                $family = $product->getFamily();

                if (null === $family) {
                    return new NullValue();
                }

                return new FamilyValue($family->getCode());
            case 'family_variant':
                $familyVariant = $product->getFamilyVariant();

                if (null === $familyVariant) {
                    return new NullValue();
                }

                return new FamilyVariantValue($familyVariant->getCode());
            case 'groups':
                $groupCodes = $product->getGroupCodes();

                if (empty($groupCodes)) {
                    return new NullValue();
                }

                return new GroupsValue($groupCodes);
            case 'parent':
                $parent = $product->getParent();

                if (null === $parent) {
                    return new NullValue();
                }

                return new ParentValue($parent->getCode());
            default:
                throw new \LogicException(sprintf('Unsupported property name "%s"', $propertyName));
        }
    }
}
