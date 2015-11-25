<?php

namespace Pim\Component\Localization\Presenter;

use Pim\Component\Localization\Factory\NumberFactory;
use Symfony\Component\Intl\Intl;

/**
 * Price presenter, able to render price readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricePresenter implements PresenterInterface
{
    /** @var NumberFactory */
    protected $numberFactory;

    /**
     * @param NumberFactory $numberFactory
     */
    public function __construct(NumberFactory $numberFactory)
    {
        $this->numberFactory = $numberFactory;
    }

    /**
     * {@inheritdoc}
     *
     * Presents a structured price to be readable. If locale option is set, the price is formatted according to
     * the locale. If no locale option is set, the default is the price amount then the currency symbol.
     */
    public function present($price, array $options = [])
    {
        return $this
            ->numberFactory
            ->create(array_merge($options, ['type' => \NumberFormatter::CURRENCY]))
            ->formatCurrency($price['data'], $price['currency']);
    }
}
