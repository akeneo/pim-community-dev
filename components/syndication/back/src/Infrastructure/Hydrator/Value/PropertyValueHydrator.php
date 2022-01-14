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

namespace Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Platform\Syndication\Application\Common\Source\PropertySource;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\CategoriesValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\CodeValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\FamilyValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\FamilyVariantValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;

class PropertyValueHydrator
{
    /**
     * @param ConnectorProduct|ConnectorProductModel $productOrProductModel
     */
    public function hydrate(PropertySource $source, $productOrProductModel): SourceValueInterface
    {
        if (
            !(
                $productOrProductModel instanceof ConnectorProduct ||
                $productOrProductModel instanceof ConnectorProductModel
            )
        ) {
            throw new \InvalidArgumentException('Cannot hydrate this entity');
        }

        switch ($source->getName()) {
            case 'code':
                if (!$productOrProductModel instanceof ConnectorProductModel) {
                    return new NullValue();
                }

                return new CodeValue($productOrProductModel->code());
            case 'categories':
                return new CategoriesValue($productOrProductModel->categoryCodes());
            case 'enabled':
                if (!$productOrProductModel instanceof ConnectorProduct) {
                    return new NullValue();
                }

                return new EnabledValue($productOrProductModel->enabled());
            case 'family':
                $familyCode = $productOrProductModel->familyCode();

                if (!$familyCode) {
                    return new NullValue();
                }

                return new FamilyValue($familyCode);
            case 'family_variant':
                if (!$productOrProductModel instanceof ConnectorProductModel) {
                    return new NullValue();
                }

                $familyVariantCode = $productOrProductModel->familyVariantCode();

                return new FamilyVariantValue($familyVariantCode);
            case 'parent':
                if (!$productOrProductModel instanceof ConnectorProduct) {
                    return new NullValue();
                }

                $parentCode = $productOrProductModel->parentProductModelCode();

                if (!$parentCode) {
                    return new NullValue();
                }

                return new ParentValue($parentCode);
            default:
                throw new \LogicException(sprintf('Unsupported property name "%s"', $source->getName()));
        }
    }
}
