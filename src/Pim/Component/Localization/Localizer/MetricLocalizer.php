<?php

namespace Pim\Component\Localization\Localizer;

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
    public function validate($metric, array $options = [], $attributeCode)
    {
        if (!isset($metric['data'])) {
            return null;
        }

        return parent::validate($metric['data'], $options, $attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function delocalize($metric, array $options = [])
    {
        if (isset($metric['data'])) {
            $metric['data'] = parent::delocalize($metric['data'], $options);
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

        if (isset($metric['data'])) {
            $metric['data'] = parent::localize($metric['data'], $options);
        }

        return $metric;
    }
}
