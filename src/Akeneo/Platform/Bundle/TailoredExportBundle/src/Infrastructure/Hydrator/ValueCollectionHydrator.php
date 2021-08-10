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
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AssociationTypeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\PropertySource;
use Akeneo\Platform\TailoredExport\Domain\Model\ValueCollection;

class ValueCollectionHydrator
{
    private ValueHydrator $valueHydrator;

    public function __construct(
        ValueHydrator $valueHydrator
    ) {
        $this->valueHydrator = $valueHydrator;
    }

    public function hydrate(
        ProductInterface $product,
        ColumnCollection $columnConfiguration
    ): ValueCollection {
        $allSources = $columnConfiguration->getAllSources();
        $valueCollection = new ValueCollection();

        foreach ($allSources as $source) {
            $value = $this->valueHydrator->hydrate($product, $source);
            if ($source instanceof AttributeSource) {
                $valueCollection->add($value, $source->getCode(), $source->getChannel(), $source->getLocale());
            } elseif ($source instanceof PropertySource) {
                $valueCollection->add($value, $source->getName(), null, null);
            } elseif ($source instanceof AssociationTypeSource) {
                $valueCollection->add($value, $source->getCode(), null, null);
            } else {
                throw new \InvalidArgumentException('Unsupported source');
            }
        }

        return $valueCollection;
    }
}
