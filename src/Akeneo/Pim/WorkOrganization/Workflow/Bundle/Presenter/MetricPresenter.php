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
 * Present change on metric data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class MetricPresenter extends AbstractProductValuePresenter implements TranslatorAwareInterface
{
    use TranslatorAware;

    /** @var BasePresenterInterface */
    protected $metricPresenter;

    /** @var LocaleResolver */
    protected $localeResolver;

    public function __construct(
        BasePresenterInterface $metricPresenter,
        LocaleResolver $localeResolver
    ) {
        $this->metricPresenter = $metricPresenter;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $attributeType, string $referenceDataName = null): bool
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

        $options = ['locale' => $this->localeResolver->getCurrentLocale()];
        $structuredMetric = ['amount' => $data->getData(), 'unit' => $data->getUnit()];

        return $this->metricPresenter->present($structuredMetric, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        $options = ['locale' => $this->localeResolver->getCurrentLocale()];

        return $this->metricPresenter->present($change['data'], $options);
    }
}
