<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Sorts products by completeness, for a provided locale and channel (it is not
 * possible to sort without).
 *
 * Product without completeness (meaning without family) are always last, no
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
    public function addFieldSorter($field, $direction, $locale = null, $channel = null)
    {
        $this->checkLocaleAndChannel($locale, $channel);

        $field .= sprintf('.%s.%s', $channel, $locale);

        parent::addFieldSorter($field, $direction, $locale, $channel);
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
