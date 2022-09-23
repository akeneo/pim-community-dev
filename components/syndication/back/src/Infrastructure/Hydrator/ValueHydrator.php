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

namespace Akeneo\Platform\Syndication\Infrastructure\Hydrator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\AttributeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\PropertySource;
use Akeneo\Platform\Syndication\Application\Common\Source\SourceInterface;
use Akeneo\Platform\Syndication\Application\Common\Source\StaticSource;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\AssociationTypeValueHydrator;
use Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\AttributeValueHydrator;
use Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\PropertyValueHydrator;
use Akeneo\Platform\Syndication\Infrastructure\Hydrator\Value\StaticValueHydrator;

class ValueHydrator
{
    private AttributeValueHydrator $attributeValueHydrator;
    private PropertyValueHydrator $propertyValueHydrator;
    private StaticValueHydrator $staticValueHydrator;
    private AssociationTypeValueHydrator $associationTypeValueHydrator;

    public function __construct(
        AttributeValueHydrator $attributeValueHydrator,
        PropertyValueHydrator $propertyValueHydrator,
        StaticValueHydrator $staticValueHydrator,
        AssociationTypeValueHydrator $associationTypeValueHydrator
    ) {
        $this->attributeValueHydrator = $attributeValueHydrator;
        $this->propertyValueHydrator = $propertyValueHydrator;
        $this->staticValueHydrator = $staticValueHydrator;
        $this->associationTypeValueHydrator = $associationTypeValueHydrator;
    }

    /**
     * @param ConnectorProduct|ConnectorProductModel $productOrProductModel
     */
    public function hydrate(
        $productOrProductModel,
        SourceInterface $source
    ): SourceValueInterface {
        if (
            !($productOrProductModel instanceof ConnectorProduct ||
                $productOrProductModel instanceof ConnectorProductModel)
        ) {
            throw new \InvalidArgumentException('Cannot hydrate this entity');
        }

        switch (true) {
            case $source instanceof AttributeSource:
                $value = $productOrProductModel->values()->filter(function (ValueInterface $value) use ($source) {
                    return $value->getAttributeCode() === $source->getCode() && $value->getScopeCode() === $source->getChannel() && $value->getLocaleCode() === $source->getLocale();
                })->first();

                return $this->attributeValueHydrator->hydrate(false === $value ? null : $value, $source->getAttributeType(), $productOrProductModel);
            case $source instanceof PropertySource:
                return $this->propertyValueHydrator->hydrate($source, $productOrProductModel);
            case $source instanceof StaticSource:
                return $this->staticValueHydrator->hydrate($source, $productOrProductModel);
            case $source instanceof AssociationTypeSource:
                return $this->associationTypeValueHydrator->hydrate($productOrProductModel, $source->getCode(), $source->isQuantified());
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported source type "%s"', get_class($source)));
        }
    }
}
