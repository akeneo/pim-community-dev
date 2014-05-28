<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 * Present metric data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MetricPresenter extends AbstractPresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('metric', $change);
    }

    /**
     * {@inheritdoc}
     */
    public function present($data, array $change)
    {
        return $this
            ->factory
            ->create(
                sprintf('%s %s', $data->getData(), $data->getUnit()),
                sprintf('%s %s', $change['metric']['data'], $change['metric']['unit'])
            )
            ->render($this->renderer);
    }
}
