<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Presenter\NumberPresenter;

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

        if (array_key_exists('amount', $prices) && array_key_exists('currency', $prices)) {
            return $this->getPrice($numberFormatter, $prices);
        }

        $presentedPrices = [];
        foreach ($prices as $price) {
            if ('' !== $presentedPrice = $this->getPrice($numberFormatter, $price)) {
                $presentedPrices[] = $presentedPrice;
            }
        }

        return implode(', ', $presentedPrices);
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

        return ['amount' => (float) $price, 'currency' => $currency];
    }

    /**
     * Get price with currency only if data is not null
     * (if data is null and formatted by formatCurrency(), it will return 0)
     *
     * @param \NumberFormatter $numberFormatter
     * @param array            $price
     *
     * @return string
     */
    protected function getPrice(\NumberFormatter $numberFormatter, array $price)
    {
        if (!isset($price['amount'])) {
            return '';
        }

        return $numberFormatter->formatCurrency($price['amount'], $price['currency']);
    }
}
