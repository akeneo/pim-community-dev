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
            if (isset($price['data']) && !parent::isValid($price['data'], $options, $attributeCode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delocalize($prices, array $options = [])
    {
        foreach ($prices as $index => $price) {
            if (isset($price['data'])) {
                $prices[$index]['data'] = parent::delocalize($price['data'], $options);
            }
        }

        return $prices;
    }

    /**
     * {@inheritdoc}
     */
    public function localize($prices, array $options = [])
    {
        foreach ($prices as $index => $price) {
            $prices[$index]['data'] = parent::localize($price['data'], $options);
        }

        return $prices;
    }
}
