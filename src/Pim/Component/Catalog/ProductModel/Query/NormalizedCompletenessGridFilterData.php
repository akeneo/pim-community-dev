<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\Query;

/**
 * Object that represents the data used by the completeness filter to filter the grid.
 * Those data will be index in the product and product model ES index.
 *
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
     * Return an array of "integer" indexed by channel and locale:
     *    - 1 means that there is at least variant product complete
     *    - 0 means that all variant product are incomplete
     *
     * This method will return an array loke that:
     * [
     *      'ecommerce' => [
     *            'en_US => 1
     *      ]
     *      'tablet' => [
     *            'en_US => 1
     *            'fr_FR => 1
     *      ]
     * ]
     *
     * @return array
     */
    public function atLeastComplete(): array
    {
        $normalizedData = [];
        foreach ($this->flatData as $row) {
            list($channel, $locale, $complete) = array_values($row);

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
     * Return an array of "integer" indexed by channel and locale:
     *    - 1 means that there is at least variant product incomplete
     *    - 0 means that all variant product are complete
     *
     * This method will return an array loke that:
     * [
     *      'ecommerce' => [
     *            'en_US => 1
     *      ]
     *      'tablet' => [
     *            'en_US => 1
     *            'fr_FR => 1
     *      ]
     * ]
     *
     * @return array
     */
    public function atLeastIncomplete(): array
    {
        $normalizedData = [];
        foreach ($this->flatData as $row) {
            list($channel, $locale, $complete, $incomplete) = array_values($row);

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
