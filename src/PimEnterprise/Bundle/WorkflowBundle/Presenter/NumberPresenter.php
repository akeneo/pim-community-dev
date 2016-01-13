<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Component\Localization\LocaleResolver;
use Pim\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;

/**
 * Present changes on numbers
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class NumberPresenter extends AbstractProductValuePresenter
{
    /** @var BasePresenterInterface */
    protected $numberPresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param BasePresenterInterface $numberPresenter
     * @param LocaleResolver         $localeResolver
     */
    public function __construct(BasePresenterInterface $numberPresenter, LocaleResolver $localeResolver)
    {
        $this->numberPresenter = $numberPresenter;
        $this->localeResolver  = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return AttributeTypes::NUMBER === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->numberPresenter->present($data, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->numberPresenter->present(array_pop($change), $options);
    }
}
