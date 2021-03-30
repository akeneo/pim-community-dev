<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;

/**
 * Present changes on prices
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PricesPresenter extends AbstractProductValuePresenter
{
    /** @var BasePresenterInterface */
    protected $pricesPresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    public function __construct(
        BasePresenterInterface $pricesPresenter,
        LocaleResolver $localeResolver
    ) {
        $this->pricesPresenter = $pricesPresenter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
    {
        return AttributeTypes::PRICE_COLLECTION === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function present($formerData, array $change)
    {
        $value = $this->normalizeData($formerData);
        $change = $this->normalizeChange($change);

        foreach ($value as $currency => $price) {
            if (isset($change[$currency]) && $price === $change[$currency]) {
                unset($change[$currency]);
                unset($value[$currency]);
            }
        }

        foreach ($change as $currency => $price) {
            if ('' === $price) {
                unset($change[$currency]);
            }
        }

        return [
            'before_data' => array_values($value),
            'after_data' => array_values($change),
        ];

        return $this->renderer->renderDiff(array_values($value), array_values($change));
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        if (! is_iterable($data)) {
            return [];
        }

        $prices = [];
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        foreach ($data as $price) {
            $amount = $price->getData();

            if (null !== $amount) {
                $structuredPrice = ['amount' => $amount, 'currency' => $price->getCurrency()];
                $prices[$price->getCurrency()] = $this->pricesPresenter->present($structuredPrice, $options);
            }
        }

        return $prices;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $prices = [];
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        foreach ($change['data'] as $price) {
            $prices[$price['currency']] = $this->pricesPresenter->present($price, $options);
        }

        return $prices;
    }
}
