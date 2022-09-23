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
use Akeneo\Platform\Syndication\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\Syndication\Application\Common\Source\AttributeSource;
use Akeneo\Platform\Syndication\Application\Common\Source\PropertySource;
use Akeneo\Platform\Syndication\Application\Common\Source\StaticSource;
use Akeneo\Platform\Syndication\Application\Common\ValueCollection;

class ValueCollectionHydrator
{
    private ValueHydrator $valueHydrator;

    public function __construct(
        ValueHydrator $valueHydrator
    ) {
        $this->valueHydrator = $valueHydrator;
    }

    /**
     * @param ConnectorProduct|ConnectorProductModel $productOrProductModel
     */
    public function hydrate(
        $productOrProductModel,
        ColumnCollection $columnConfiguration
    ): ValueCollection {
        if (
            !($productOrProductModel instanceof ConnectorProduct || $productOrProductModel instanceof ConnectorProductModel)
        ) {
            throw new \InvalidArgumentException('Cannot hydrate this entity');
        }

        $allSources = $columnConfiguration->getAllSources();
        $valueCollection = new ValueCollection();

        foreach ($allSources as $source) {
            $value = $this->valueHydrator->hydrate($productOrProductModel, $source);

            switch (true) {
                case $source instanceof AttributeSource:
                    $valueCollection->add($value, $source->getUuid(), $source->getChannel(), $source->getLocale());
                    break;
                case $source instanceof PropertySource:
                    $valueCollection->add($value, $source->getUuid(), null, null);
                    break;
                case $source instanceof StaticSource:
                    $valueCollection->add($value, $source->getUuid(), null, null);
                    break;
                default:
                    throw new \InvalidArgumentException('Unsupported source');
            }
        }

        return $valueCollection;
    }
}
