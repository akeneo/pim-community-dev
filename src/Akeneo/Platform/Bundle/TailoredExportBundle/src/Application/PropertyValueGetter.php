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

namespace Akeneo\Platform\TailoredExport\Application;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\CategoriesValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FamilyValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\ParentValue;

class PropertyValueGetter
{
    public function get(string $propertyName, ProductInterface $product)
    {
        switch ($propertyName) {
            case 'categories':
                return new CategoriesValue($product->getCategoryCodes());
            case 'enabled':
                return new EnabledValue($product->isEnabled());
            case 'family':
                $familyCode = $product->getFamily() ? $product->getFamily()->getCode() : '';

                return new FamilyValue($familyCode);
            case 'family_variant':
                $familyVariantCode = $product->getFamilyVariant() ? $product->getFamilyVariant()->getCode() : '';

                return new FamilyVariantValue($familyVariantCode);
            case 'groups':
                return new GroupsValue($product->getGroupCodes());
            case 'parent':
                $parentCode = $product->getParent() ? $product->getParent()->getCode() : '';

                return new ParentValue($parentCode);
            default:
                throw new \Exception();
        }
    }
}
