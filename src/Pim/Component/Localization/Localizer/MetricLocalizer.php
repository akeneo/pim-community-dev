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
    public function isValid($metric, array $options = [])
    {
        if (!isset($metric['data'])) {
            return true;
        }

        return $this->isValidNumber($metric['data'], $options);
    }

    /**
     * {@inheritdoc}
     */
    public function convertLocalizedToDefault($metric)
    {
        if (isset($metric['data'])) {
            $metric['data'] = $this->convertNumber($metric['data']);
        }

        return $metric;
    }
}
