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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Hydrator;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\SourceInterface;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\AssociationTypeValueHydrator;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\AttributeValueHydrator;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\PropertyValueHydrator;

class ValueHydrator
{
    public function __construct(
        private AttributeValueHydrator $attributeValueHydrator,
        private PropertyValueHydrator $propertyValueHydrator,
        private AssociationTypeValueHydrator $associationTypeValueHydrator,
    ) {
    }

    public function hydrate(
        ProductInterface|ProductModelInterface $productOrProductModel,
        SourceInterface $source,
    ): SourceValueInterface {
        switch (true) {
            case $source instanceof AttributeSource:
                $value = $productOrProductModel->getValue($source->getCode(), $source->getLocale(), $source->getChannel());

                return $this->attributeValueHydrator->hydrate($value, $source->getAttributeType(), $productOrProductModel);
            case $source instanceof PropertySource:
                return $this->propertyValueHydrator->hydrate($source, $productOrProductModel);
            case $source instanceof AssociationTypeSource:
                return $this->associationTypeValueHydrator->hydrate($productOrProductModel, $source->getCode(), $source->isQuantified());
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported source type "%s"', $source::class));
        }
    }
}
