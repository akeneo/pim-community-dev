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
 * Present change on metric data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class MetricPresenter extends AbstractProductValuePresenter implements TranslatorAwareInterface
{
    use TranslatorAware;

    /** @var LocalizerInterface */
    protected $metricLocalizer;

    /** @var LocaleResolver */
    protected $localeResolver;

    /**
     * @param LocalizerInterface $metricLocalizer
     * @param LocaleResolver     $localeResolver
     */
    public function __construct(LocalizerInterface $metricLocalizer, LocaleResolver $localeResolver)
    {
        $this->metricLocalizer = $metricLocalizer;
        $this->localeResolver  = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return AttributeTypes::METRIC === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        if (null === $data) {
            return '';
        }

        $locale = $this->localeResolver->getCurrentLocale();
        $localizedData = $this->metricLocalizer->localize($data->getData(), ['locale' => $locale]);

        return sprintf('%s %s', $localizedData, $this->translator->trans($data->getUnit()));
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $locale = $this->localeResolver->getCurrentLocale();
        $localizedData = $this->metricLocalizer->localize($change['data']['data'], ['locale' => $locale]);

        return sprintf('%s %s', $localizedData, $this->translator->trans($change['data']['unit']));
    }
}
