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
use Akeneo\Platform\Syndication\Application\Common\Source\StaticSource;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\BooleanValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;

class StaticValueHydrator
{
    /**
     * @param ConnectorProduct $productOrProductModel
     */
    public function hydrate(StaticSource $source, $productOrProductModel): SourceValueInterface
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
            case 'boolean':
                return new BooleanValue($source->getValue());
            case 'string':
                return new StringValue($source->getValue());
            case 'measurement':
                return new MeasurementValue($source->getValue()['value'], $source->getValue()['unit']);
            default:
                throw new \LogicException(sprintf('Unsupported property name "%s"', $source->getName()));
        }
    }
}
