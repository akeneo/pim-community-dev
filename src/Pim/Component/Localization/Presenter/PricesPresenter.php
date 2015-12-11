<?php

namespace Pim\Component\Localization\Presenter;

/**
 * Price presenter, able to render price readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesPresenter extends NumberPresenter
{
    /**
     * {@inheritdoc}
     *
     * Presents a structured price set to be readable. If locale option is set, the prices are formatted according to
     * the locale. If no locale option is set, the default is the price amount then the currency symbol.
     */
    public function present($prices, array $options = [])
    {
        if ('' === $prices || null === $prices) {
            return $prices;
        }

        if (!is_array($prices)) {
            if (!isset($options['versioned_attribute'])) {
                return parent::present($prices, $options);
            }

            $prices = $this->getStructuredPrice($prices, $options['versioned_attribute']);
        }

        $numberFormatter = $this
            ->numberFactory
            ->create(array_merge($options, ['type' => \NumberFormatter::CURRENCY]));

        if (isset($prices['data']) && isset($prices['currency'])) {
            return $numberFormatter->formatCurrency($prices['data'], $prices['currency']);
        }

        $presentedPrices = [];
        foreach ($prices as $price) {
            $presentedPrices[] = $numberFormatter->formatCurrency($price['data'], $price['currency']);
        };

        return $presentedPrices;
    }

    /**
     * Get the price with format data and currency from the versioned attribute.
     * The versionedAttribute looks like 'price-EUR', and the price is a string representing a number.
     *
     * @param string $price
     * @param string $versionedAttribute
     *
     * @return array
     */
    protected function getStructuredPrice($price, $versionedAttribute)
    {
        $parts = preg_split('/-/', $versionedAttribute);
        $currency = end($parts);

        return ['data' => (float) $price, 'currency' => $currency];
    }
}
