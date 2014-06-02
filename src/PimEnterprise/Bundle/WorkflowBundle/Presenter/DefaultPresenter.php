<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 * Present data without pre-transformation
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DefaultPresenter extends AbstractProductValuePresenter
{
    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (isset($change['id'])) {
            unset($change['id']);
        }

        return array_pop($change);
    }
}
