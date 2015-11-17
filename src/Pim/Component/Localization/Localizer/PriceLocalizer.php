<?php

namespace Pim\Component\Localization\Localizer;

use Symfony\Component\Validator\ConstraintViolationList;

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
    public function validate($prices, array $options = [], $attributeCode)
    {
        $violations = new ConstraintViolationList();
        foreach ($prices as $price) {
            if (isset($price['data']) && $valid = parent::validate($price['data'], $options, $attributeCode)) {
                $violations->addAll($valid);
            }
        }

        return ($violations->count() > 0) ? $violations : null;
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
        if (!is_array($prices)) {
            return parent::localize($prices, $options);
        }

        foreach ($prices as $index => $price) {
            $prices[$index]['data'] = parent::localize($price['data'], $options);
        }

        return $prices;
    }
}
