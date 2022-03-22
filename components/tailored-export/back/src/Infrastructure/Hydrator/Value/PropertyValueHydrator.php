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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\CategoriesValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\CodeValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FamilyValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QualityScoreValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\FindQualityScoresInterface;

class PropertyValueHydrator
{
    public function __construct(
        private FindQualityScoresInterface $findQualityScores,
    ) {
    }

    public function hydrate(
        PropertySource $source,
        ProductInterface|ProductModelInterface $productOrProductModel,
    ): SourceValueInterface {
        switch ($source->getName()) {
            case 'code':
                if (!$productOrProductModel instanceof ProductModelInterface) {
                    throw new \InvalidArgumentException('Cannot hydrate enabled value on Product entity');
                }

                return new CodeValue($productOrProductModel->getCode());
            case 'categories':
                return new CategoriesValue($productOrProductModel->getCategoryCodes());
            case 'enabled':
                if (!$productOrProductModel instanceof ProductInterface) {
                    throw new \InvalidArgumentException('Cannot hydrate enabled value on ProductModel entity');
                }

                return new EnabledValue($productOrProductModel->isEnabled());
            case 'family':
                $family = $productOrProductModel->getFamily();

                if (!$family instanceof FamilyInterface) {
                    return new NullValue();
                }

                return new FamilyValue($family->getCode());
            case 'family_variant':
                $familyVariant = $productOrProductModel->getFamilyVariant();

                if (!$familyVariant instanceof FamilyVariantInterface) {
                    return new NullValue();
                }

                return new FamilyVariantValue($familyVariant->getCode());
            case 'groups':
                if (!$productOrProductModel instanceof ProductInterface) {
                    throw new \InvalidArgumentException('Cannot hydrate groups value on ProductModel entity');
                }

                $groupCodes = $productOrProductModel->getGroupCodes();

                if (empty($groupCodes)) {
                    return new NullValue();
                }

                return new GroupsValue($groupCodes);
            case 'parent':
                $parent = $productOrProductModel->getParent();

                if (!$parent instanceof ProductModelInterface) {
                    return new NullValue();
                }

                return new ParentValue($parent->getCode());
            case 'quality_score':
                if (!$productOrProductModel instanceof ProductInterface) {
                    throw new \InvalidArgumentException('Cannot hydrate Quality Score value on ProductModel entity');
                }

                $qualityScore = $this->findQualityScores->forProduct(
                    $productOrProductModel->getIdentifier(),
                    $source->getChannel(),
                    $source->getLocale(),
                );

                if (null === $qualityScore) {
                    return new NullValue();
                }

                return new QualityScoreValue($qualityScore);
            default:
                throw new \LogicException(sprintf('Unsupported property name "%s"', $source->getName()));
        }
    }
}
