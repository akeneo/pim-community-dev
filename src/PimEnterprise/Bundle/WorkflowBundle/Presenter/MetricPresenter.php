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

/**
 * Present change on metric data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class MetricPresenter extends AbstractProductValuePresenter implements TranslatorAwareInterface
{
    use TranslatorAware;

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return 'pim_catalog_metric' === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        if (null === $data) {
            return '';
        }

        return sprintf('%s %s', $data->getData(), $this->translator->trans($data->getUnit()));
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return sprintf('%s %s', $change['value']['data'], $this->translator->trans($change['value']['unit']));
    }
}
