<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Localizer\NumberLocalizer;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Check and convert if price provided respects the format expected
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceLocalizer extends NumberLocalizer
{
    /**
     * {@inheritdoc}
     */
    public function validate($prices, $attributeCode, array $options = [])
    {
        $violations = new ConstraintViolationList();
        foreach ($prices as $price) {
            if (isset($price['amount']) && $valid = parent::validate($price['amount'], $attributeCode, $options)) {
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
            if (isset($price['amount'])) {
                $prices[$index]['amount'] = parent::delocalize($price['amount'], $options);
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

        if (array_key_exists('amount', $prices) && array_key_exists('currency', $prices)) {
            $prices['amount'] = parent::localize($prices['amount'], $options);
        } else {
            foreach ($prices as $index => $price) {
                $prices[$index]['amount'] = parent::localize($price['amount'], $options);
            }
        }

        return $prices;
    }
}
