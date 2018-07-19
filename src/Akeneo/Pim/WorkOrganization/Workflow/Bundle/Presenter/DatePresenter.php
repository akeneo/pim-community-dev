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

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface as BasePresenterInterface;
use Pim\Bundle\EnrichBundle\Resolver\LocaleResolver;
use Pim\Component\Catalog\AttributeTypes;

/**
 * Present changes on date data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class DatePresenter extends AbstractProductValuePresenter
{
    /** @var BasePresenterInterface */
    protected $datePresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param BasePresenterInterface $datePresenter
     * @param LocaleResolver         $localeResolver
     */
    public function __construct(BasePresenterInterface $datePresenter, LocaleResolver $localeResolver)
    {
        $this->datePresenter = $datePresenter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return AttributeTypes::DATE === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->datePresenter->present($data, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (empty($change['data'])) {
            return '';
        }

        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->datePresenter->present($change['data'], $options);
    }
}
