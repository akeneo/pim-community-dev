<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for prices
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesComparator implements ComparatorInterface
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
        $default = ['locale' => null, 'scope' => null, 'data' => []];
        $originals = array_merge($default, $originals);

        $originalPrices = [];
        foreach ($originals['data'] as $price) {
            if (null !== $price['amount']) {
                $originalPrices[$price['currency']] = $price['amount'];
                if (is_numeric($price['amount'])) {
                    $originalPrices[$price['currency']] = number_format($price['amount'], 4);
                }
            }
        }

        $dataPrices = [];
        foreach ($data['data'] as $price) {
            if (null !== $price['amount']) {
                $dataPrices[$price['currency']] = $price['amount'];
                if (is_numeric($price['amount'])) {
                    $dataPrices[$price['currency']] = number_format($price['amount'], 4);
                }
            }
        }

        if ($dataPrices !== $originalPrices) {
            $data['data'] = array_filter($data['data'], function (array $price) {
                return null !== $price['amount'];
            });
            return $data;
        }

        return null;
    }
}
