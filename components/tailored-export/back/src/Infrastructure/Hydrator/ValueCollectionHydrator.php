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
use Akeneo\Platform\TailoredExport\Application\Common\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Common\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Application\Common\ValueCollection;

class ValueCollectionHydrator
{
    public function __construct(
        private ValueHydrator $valueHydrator,
    ) {
    }

    public function hydrate(
        ProductInterface|ProductModelInterface $productOrProductModel,
        ColumnCollection $columnConfiguration,
    ): ValueCollection {
        $allSources = $columnConfiguration->getAllSources();
        $valueCollection = new ValueCollection();

        foreach ($allSources as $source) {
            $value = $this->valueHydrator->hydrate($productOrProductModel, $source);

            match (true) {
                $source instanceof AttributeSource => $valueCollection->add($value, $source->getCode(), $source->getChannel(), $source->getLocale()),
                $source instanceof PropertySource => $valueCollection->add($value, $source->getName(), null, null),
                $source instanceof AssociationTypeSource => $valueCollection->add($value, $source->getCode(), null, null),
                default => throw new \InvalidArgumentException('Unsupported source'),
            };
        }

        return $valueCollection;
    }
}
