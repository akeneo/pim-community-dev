<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Localizer\NumberLocalizer;

/**
 * Check and convert if metric provided respects the format expected
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricLocalizer extends NumberLocalizer
{
    /**
     * {@inheritdoc}
     */
    public function validate($metric, $attributeCode, array $options = [])
    {
        if (!isset($metric['amount'])) {
            return null;
        }

        return parent::validate($metric['amount'], $attributeCode, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function delocalize($metric, array $options = [])
    {
        if (isset($metric['amount'])) {
            $metric['amount'] = parent::delocalize($metric['amount'], $options);
        }

        return $metric;
    }

    /**
     * {@inheritdoc}
     */
    public function localize($metric, array $options = [])
    {
        if (!is_array($metric)) {
            return parent::localize($metric, $options);
        }

        if (isset($metric['amount'])) {
            $metric['amount'] = parent::localize($metric['amount'], $options);
        }

        return $metric;
    }
}
