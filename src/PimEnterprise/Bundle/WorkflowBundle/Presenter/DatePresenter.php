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
 * Present changes on date data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class DatePresenter extends AbstractProductValuePresenter
{
    /** @staticvar string The format that'll be used to compare date in the html diff */
    const DATE_FORMAT = 'F, d Y';

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return 'pim_catalog_date' === $attributeType;
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
