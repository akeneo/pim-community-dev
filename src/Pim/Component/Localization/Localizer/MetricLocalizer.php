<?php

namespace Pim\Component\Localization\Localizer;

/**
 * Check and convert if metric provided respects the format expected
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricLocalizer extends AbstractNumberLocalizer
{
    /**
     * {@inheritdoc}
     */
    public function isValid($metric, array $options = [], $attributeCode)
    {
        if (!isset($metric['data'])) {
            return true;
        }

        return parent::isValid($metric['data'], $options, $attributeCode);
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
    public function convertDefaultToLocalized($metric, array $options = [])
    {
        $metric['data'] = parent::convertDefaultToLocalized($metric['data'], $options);

        return $metric;
    }
}
