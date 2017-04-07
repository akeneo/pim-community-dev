<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Sorter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;

/**
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
