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
 * Present change data into HTML
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
interface PresenterInterface
{
    /**
     * Whether or not this class can present the provided change
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function supports($data);

    /**
     * Present the original part of a change into html
     *
     * @param mixed $data
     * @param array $change
     *
     * @return string
     */
    public function presentOriginal($data, array $change);

    /**
     * Present the new part of a change into html
     *
     * @param mixed $data
     * @param array $change
     *
     * @return string
     */
    public function presentNew($data, array $change);
}
