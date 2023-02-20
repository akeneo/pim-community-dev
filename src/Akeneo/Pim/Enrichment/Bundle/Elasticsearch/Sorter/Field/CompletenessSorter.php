<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Sorts products by completeness, for a provided locale and channel (it is not
 * possible to sort without).
 *
 * Products without completeness (meaning without family) are always last, no
 * matter they are sorted ascending or descending.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CompletenessSorter extends BaseFieldSorter
{
    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction, $locale = null, $channel = null): FieldSorterInterface
    {
        $this->checkLocaleAndChannel($locale, $channel);

        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the sorter.');
        }

        $field = \sprintf('completeness.%s.%s', $channel, $locale);
        $order = match ($direction) {
            Directions::ASCENDING => 'ASC',
            Directions::DESCENDING => 'DESC',
            default => throw InvalidDirectionException::notSupported($direction, static::class),
        };

        $this->searchQueryBuilder->addSort(
            [
                $field => [
                    'order' => $order,
                    'missing' => '_last',
                    'unmapped_type' => 'integer',
                ],
            ]
        );

        return $this;
    }

    /**
     * Check if channel and value are valid
     *
     * @param string $locale
     * @param string $channel
     *
     * @throws InvalidPropertyException
     */
    protected function checkLocaleAndChannel($locale, $channel)
    {
        if (null === $locale) {
            throw InvalidPropertyException::valueNotEmptyExpected('locale', static::class);
        }

        if (null === $channel) {
            throw InvalidPropertyException::valueNotEmptyExpected('scope', static::class);
        }
    }
}
