<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Localizer\LocalizerInterface;

/**
 * Present changes on prices
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PricesPresenter extends AbstractProductValuePresenter
{
    /** @var LocalizerInterface */
    protected $priceLocalizer;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param LocalizerInterface $priceLocalizer
     * @param LocaleResolver     $localeResolver
     */
    public function __construct(LocalizerInterface $priceLocalizer, LocaleResolver $localeResolver)
    {
        $this->priceLocalizer = $priceLocalizer;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return AttributeTypes::PRICE_COLLECTION === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function presentOriginal($data, array $change)
    {
        $data = $this->normalizeData($data->getData());
        $change = $this->normalizeChange($change);

        foreach ($data as $currency => $price) {
            if (!isset($change[$currency]) || isset($change[$currency]) && $price === $change[$currency]) {
                unset($data[$currency]);
                unset($change[$currency]);
            }
        }

        return $this->renderer->renderOriginalDiff(array_values($data), array_values($change));
    }

    /**
     * {@inheritdoc}
     */
    public function presentNew($data, array $change)
    {
        $data = $this->normalizeData($data->getData());
        $change = $this->normalizeChange($change);

        foreach ($data as $currency => $price) {
            if (!isset($change[$currency]) || isset($change[$currency]) && $price === $change[$currency]) {
                unset($data[$currency]);
                unset($change[$currency]);
            }
        }

        return $this->renderer->renderNewDiff(array_values($data), array_values($change));
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $prices = [];
        $formats = $this->localeResolver->getFormats();

        foreach ($data as $price) {
            $amount = $price->getData();
            if (null !== $amount) {
                $localizedAmount = $this->priceLocalizer->localize($amount, $formats);
                $prices[$price->getCurrency()] = sprintf('%s %s', $localizedAmount, $price->getCurrency());
            }
        }

        return $prices;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $formats = $this->localeResolver->getFormats();
        $localizedPrices = $this->priceLocalizer->localize($change['data'], $formats);

        $prices = [];
        foreach ($localizedPrices as $price) {
            $prices[$price['currency']] = sprintf('%s %s', $price['data'], $price['currency']);
        }

        return $prices;
    }
}
