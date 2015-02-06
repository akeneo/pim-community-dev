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
 * Present data without pre-transformation
 *
 * @author Gildas Quemener <gildas@akeneo.com>
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
        if (isset($change['__context__'])) {
            unset($change['__context__']);
        }

        return array_pop($change);
    }
}
