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

/**
 * Present changes on prices
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PricesPresenter extends AbstractProductValuePresenter
{
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
    public function present($data, array $change)
    {
        $data = $this->normalizeData($data->getData());
        $change = $this->normalizeChange($change);

        foreach ($data as $currency => $price) {
            if (!isset($change[$currency]) || isset($change[$currency]) && $price === $change[$currency]) {
                unset($data[$currency]);
                unset($change[$currency]);
            }
        }

        return $this->renderer->renderDiff(array_values($data), array_values($change));
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $prices = [];
        foreach ($data as $price) {
            if (null !== $money = $price->getData()) {
                $prices[$price->getCurrency()] = sprintf('%s %s', $money, $price->getCurrency());
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
        foreach ($change['data'] as $price) {
            $prices[$price['currency']] = sprintf('%s %s', $price['data'], $price['currency']);
        }

        return $prices;
    }
}
