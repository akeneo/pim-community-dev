<?php

namespace Pim\Component\Localization\Presenter;

use Pim\Component\Localization\Factory\NumberFactory;

/**
 * Price presenter, able to render price readable for a human
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesPresenter implements PresenterInterface
{
    /** @var NumberFactory */
    protected $numberFactory;

    /** @var string[] */
    protected $attributeTypes;

    /**
     * @param NumberFactory $numberFactory
     * @param string[]      $attributeTypes
     */
    public function __construct(NumberFactory $numberFactory, array $attributeTypes)
    {
        $this->numberFactory  = $numberFactory;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     *
     * Presents a structured price set to be readable. If locale option is set, the prices are formatted according to
     * the locale. If no locale option is set, the default is the price amount then the currency symbol.
     */
    public function present($prices, array $options = [])
    {
        return array_map(function ($price) use ($options) {
            return $this
                ->numberFactory
                ->create(array_merge($options, ['type' => \NumberFormatter::CURRENCY]))
                ->formatCurrency($price['data'], $price['currency']);
            }, $prices
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }
}
