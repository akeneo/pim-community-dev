<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

/**
 * Present change data into HTML
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface PresenterInterface
{
    /**
     * Wether or not this class can present the provided change
     *
     * @param array $change
     *
     * @return boolean
     */
    public function supportsChange(array $change);

    /**
     * Present the provided change into html
     *
     * @param mixed $data
     * @param array $change
     *
     * @return string
     */
    public function present($data, array $change);
}
