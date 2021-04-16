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

/**
 * A product value diff presenter
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
abstract class AbstractProductValuePresenter implements PresenterInterface
{
    /**
     * {@inheritdoc}
     */
    public function present($formerData, array $change)
    {
        return [
            'before' => $this->normalizeData($formerData),
            'after' => $this->normalizeChange($change),
        ];
    }

    /**
     * Normalize data
     *
     * @return array|string
     */
    protected function normalizeData($data)
    {
        return [];
    }

    /**
     * Normalize change
     *
     * @param array $change
     *
     * @return array|string
     */
    protected function normalizeChange(array $change)
    {
        return [];
    }
}
