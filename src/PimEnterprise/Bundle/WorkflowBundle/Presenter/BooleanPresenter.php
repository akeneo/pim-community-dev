<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 * Present changes on boolean data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class BooleanPresenter extends AbstractProductValuePresenter
{
    use TranslatorAware;

    /** @staticvar boolean */
    const YES = 'Yes';

    /** @staticvar boolean */
    const NO = 'No';

    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('boolean', $change);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $this->translator->trans($data ? self::YES : self::NO);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $this->translator->trans($change['boolean'] ? self::YES : self::NO);
    }
}
