<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\MassAction;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler\MassActionHandlerInterface;

/**
 * MassActionHandler registry
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassActionHandlerRegistry
{
    /** @var array $handlers */
    protected $handlers = [];

    /**
     * Add handler service in registry
     *
     * @param string                     $alias
     * @param MassActionHandlerInterface $handler
     *
     * @throws \LogicException
     */
    public function addHandler($alias, MassActionHandlerInterface $handler)
    {
        if (isset($this->handlers[$alias])) {
            throw new \LogicException(
                sprintf('"%s" alias is already defined for service handler "%s"', $alias, $handler)
            );
        }

        $this->handlers[$alias] = $handler;
    }

    /**
     * Get mass action handler
     *
     * @param string $alias
     *
     * @throws \LogicException
     *
     * @return MassActionHandlerInterface
     */
    public function getHandler($alias)
    {
        if (!isset($this->handlers[$alias])) {
            throw new \LogicException(sprintf('"%s" alias is unknown', $alias));
        }

        return $this->handlers[$alias];
    }
}
