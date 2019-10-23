<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for metrics
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricComparator implements ComparatorInterface
{
    /** @var array */
    protected $types;

    /**
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, $this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'data' => [
            'amount' => null,
            'unit'   => null,
        ]];
        $originals = array_merge($default, $originals);

        if (!isset($data['data']['amount']) && !isset($originals['data']['amount'])) {
            return null;
        }

        if (null === $data['data']['amount']) {
            return [
                'scope' => $data['scope'],
                'locale' => $data['locale'],
                'data' => null,
            ];
        }
        if (!is_numeric($data['data']['amount'])) {
            return $data;
        }

        $tmpData = $data;
        $tmpData['data']['amount'] = (float) $tmpData['data']['amount'];
        $originals['data']['amount'] = (float) $originals['data']['amount'];

        $diff = array_diff_assoc((array) $tmpData['data'], (array) $originals['data']);

        if (!empty($diff)) {
            return $data;
        }

        return null;
    }
}
