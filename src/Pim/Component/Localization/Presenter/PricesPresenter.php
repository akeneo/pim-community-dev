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
        if (!is_array($prices)) {
            return parent::present($prices, $options);
        }

        $numberFormatter = $this
            ->numberFactory
            ->create(array_merge($options, ['type' => \NumberFormatter::CURRENCY]));

        if (array_key_exists('data', $prices) && array_key_exists('currency', $prices)) {
            return $numberFormatter->formatCurrency($prices['data'], $prices['currency']);
        }

        $presentedPrices = [];
        foreach ($prices as $price) {
            $presentedPrices[] = $numberFormatter->formatCurrency($price['data'], $price['currency']);
        };

        return $presentedPrices;
    }
}
