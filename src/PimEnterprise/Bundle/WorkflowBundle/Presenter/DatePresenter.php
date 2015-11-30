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
 * Present changes on date data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class DatePresenter extends AbstractProductValuePresenter
{
    /** @var BaseDatePresenter */
    protected $datePresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param BaseDatePresenter $datePresenter
     * @param LocaleResolver    $localeResolver
     */
    public function __construct(BaseDatePresenter $datePresenter, LocaleResolver $localeResolver)
    {
        $this->datePresenter  = $datePresenter;
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
        return $data instanceof \DateTime ? $data->format(self::DATE_FORMAT) : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return !empty($change['data']) ? (new \DateTime($change['data']))->format(self::DATE_FORMAT) : '';
    }
}
