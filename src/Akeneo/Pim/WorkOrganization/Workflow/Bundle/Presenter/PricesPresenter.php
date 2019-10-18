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

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

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
        IdentifiableObjectRepositoryInterface $attributeRepository,
        BasePresenterInterface $pricesPresenter,
        LocaleResolver $localeResolver
    ) {
        parent::__construct($attributeRepository);

        $this->pricesPresenter = $pricesPresenter;
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
    public function present(ValueInterface $value, array $change)
    {
        $value = $this->normalizeData($value->getData());
        $change = $this->normalizeChange($change);

        foreach ($value as $currency => $price) {
            if (!isset($change[$currency]) || (isset($change[$currency]) && $price === $change[$currency])) {
                unset($value[$currency]);
                unset($change[$currency]);
            }
        }

        foreach ($change as $currency => $price) {
            if ('' === $price) {
                unset($change[$currency]);
            }
        }

        return $this->renderer->renderDiff(array_values($value), array_values($change));
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
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
