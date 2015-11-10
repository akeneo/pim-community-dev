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
use Pim\Component\Localization\Localizer\LocalizerInterface;

/**
 * Present changes on numbers
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class NumberPresenter extends AbstractProductValuePresenter
{
    /** @var LocalizerInterface */
    protected $numberLocalizer;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param LocalizerInterface $numberLocalizer
     * @param LocaleResolver     $localeResolver
     */
    public function __construct(LocalizerInterface $numberLocalizer, LocaleResolver $localeResolver)
    {
        $this->numberLocalizer = $numberLocalizer;
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
        $locale = $this->localeResolver->getCurrentLocale();

        return $this->numberLocalizer->localize($data, ['locale' => $locale]);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $locale = $this->localeResolver->getCurrentLocale();

        return $this->numberLocalizer->localize(array_pop($change), ['locale' => $locale]);
    }
}
