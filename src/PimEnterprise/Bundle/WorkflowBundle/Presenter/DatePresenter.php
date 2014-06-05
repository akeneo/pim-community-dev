<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 * Present changes on date data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DatePresenter extends AbstractProductValuePresenter
{
    /** @staticvar string The format that'll be used to compare date in the html diff */
    const DATE_FORMAT = 'F, d Y';

    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('date', $change);
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
        return !empty($change['date']) ? (new \DateTime($change['date']))->format(self::DATE_FORMAT) : '';
    }
}
