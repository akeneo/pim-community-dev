<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductAndProductModel\Query;

/**
 * Object that represents the data used by the completeness filter to filter the grid.
 * Those data will be index in the product and product model ES index.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompleteFilterData
{
    private const DATA_STRUCTURE_RULES = [
        'channel_code' => FILTER_REQUIRE_SCALAR,
        'locale_code' => FILTER_REQUIRE_SCALAR,
        'complete' => [
            'filter' => FILTER_VALIDATE_INT,
            'flags'  => FILTER_REQUIRE_SCALAR,
            'options' => ['min_range' => 0, 'max_range' => 1]
        ],
        'incomplete' => [
            'filter' => FILTER_VALIDATE_INT,
            'flags'  => FILTER_REQUIRE_SCALAR,
            'options' => ['min_range' => 0, 'max_range' => 1]
        ],
    ];

    /** @var array */
    private $flatData;

    /**
     * @param array $flatData
     */
    public function __construct(array $flatData)
    {
        foreach ($flatData as $row) {
            $hasInvalidData = array_filter(
                filter_var_array($row, self::DATA_STRUCTURE_RULES),
                function ($item) {
                    return false === $item || null === $item;
                }
            );

            if (0 < count($hasInvalidData)) {
                throw new \InvalidArgumentException('The provided data are not valid');
            }
        }

        $this->flatData = $flatData;
    }

    /**
     * Return an array of "integer" indexed by channel and locale:
     *    - 1 means that all variant product are incomplete
     *    - 0 means that at least one product is incomplete
     *
     * This method will return an array like that:
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
    public function allIncomplete(): array
    {
        $normalizedData = [];
        foreach ($this->flatData as $row) {
            list($channel, $locale, $complete) = array_values($row);

            if (!isset($normalizedData[$channel][$locale])) {
                $normalizedData[$channel][$locale] = 1;
            }

            if (1 === (int) $complete) {
                $normalizedData[$channel][$locale] = 0;
            }
        }

        return $normalizedData;
    }

    /**
     * Return an array of "integer" indexed by channel and locale:
     *    - 1 means that all variant product are complete
     *    - 0 means that there is at least variant product incomplete
     *
     * This method will return an array like that:
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
    public function allComplete(): array
    {
        $normalizedData = [];
        foreach ($this->flatData as $row) {
            list($channel, $locale, $complete, $incomplete) = array_values($row);

            if (!isset($normalizedData[$channel][$locale])) {
                $normalizedData[$channel][$locale] = 1;
            }

            if (1 === (int) $incomplete) {
                $normalizedData[$channel][$locale] = 0;
            }
        }

        return $normalizedData;
    }
}
