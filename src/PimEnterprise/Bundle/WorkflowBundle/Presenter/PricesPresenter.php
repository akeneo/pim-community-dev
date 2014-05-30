<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PricesPresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('prices', $change);
    }

    public function present($object, array $change)
    {
        $data = $this->normalizeData($object->getData());
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
            if ($money = $price->getData()) {
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
        foreach ($change['prices'] as $price) {
            $prices[$price['currency']] = sprintf('%s %s', $price['data'], $price['currency']);
        }

        return $prices;
    }
}
