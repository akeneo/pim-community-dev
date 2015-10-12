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

/**
 * Action applier interface
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
interface ActionApplierInterface
{
    /**
     * Apply an action to a set of items
     *
     * @param ActionInterface $action
     * @param array           $items
     */
    public function applyAction(ActionInterface $action, array $items = []);

    /**
     * Does the action applier support the given action ?
     *
     * @param ActionInterface $action
     *
     * @return bool
     */
    public function supports(ActionInterface $action);
}
