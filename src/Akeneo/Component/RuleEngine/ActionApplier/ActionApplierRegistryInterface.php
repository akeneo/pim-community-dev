<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\RuleEngine\ActionApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;

/**
 * Action applier registry interface
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
interface ActionApplierRegistryInterface
{
    /**
     * Get the action applier supporting the given action
     *
     * @param ActionInterface $action
     *
     * @return ActionApplierInterface
     */
    public function getActionApplier(ActionInterface $action);

    /**
     * Add an action applier to the registry
     *
     * @param ActionApplierInterface $actionApplier
     */
    public function addActionApplier(ActionApplierInterface $actionApplier);
}
