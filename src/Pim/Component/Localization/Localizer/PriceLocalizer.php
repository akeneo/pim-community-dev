<?php

namespace Pim\Component\Localization\Localizer;

/**
 * Check and convert if price provided respects the format expected
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceLocalizer extends AbstractNumberLocalizer
{
    /**
     * {@inheritdoc}
     */
    public function isValid($prices, array $options = [], $attributeCode)
    {
        foreach ($prices as $price) {
            if (isset($price['data']) && !$this->isValidNumber($price['data'], $options, $attributeCode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function convertLocalizedToDefault($prices, array $options = [])
    {
        foreach ($prices as $i => $price) {
            if (isset($price['data'])) {
                $prices[$i]['data'] = $this->convertNumber($price['data'], $options);
            }
        }

        return $prices;
    }
}
