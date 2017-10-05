<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\Query;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizedCompletenessGridFilterData
{
    /** @var array */
    private $flatData;

    /**
     * @param array $flatData
     */
    public function __construct(array $flatData)
    {
        $this->flatData = $flatData;
    }

    /**
     * @return array
     */
    public function atLeastComplete(): array
    {
        $normalizedData = [];
        foreach ($this->flatData as $row) {
            list ($channel, $locale, $complete) = array_values($row);

            if (!isset($normalizedData[$channel][$locale])) {
                $normalizedData[$channel][$locale] = 0;
            }

            if (1 === (int) $complete) {
                $normalizedData[$channel][$locale] = 1;
            }
        }

        return $normalizedData;
    }

    /**
     * @return array
     */
    public function atLeastIncomplete(): array
    {
        $normalizedData = [];
        foreach ($this->flatData as $row) {
            list ($channel, $locale, $complete, $incomplete) = array_values($row);

            if (!isset($normalizedData[$channel][$locale])) {
                $normalizedData[$channel][$locale] = 0;
            }

            if (1 === (int) $incomplete) {
                $normalizedData[$channel][$locale] = 1;
            }
        }

        return $normalizedData;
    }
}
