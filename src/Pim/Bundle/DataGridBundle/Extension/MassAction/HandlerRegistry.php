<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction;

use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;

/**
 * Handler registry
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HandlerRegistry
{
    /** @var array $handlerIds */
    protected $handlerIds = array();

    /**
     * Add handler service in registry
     *
     * @param string $alias
     * @param string $serviceId
     *
     * @throws \LogicException
     */
    public function addHandler($alias, MassActionHandlerInterface $handler)
    {
        if (isset($this->handlerIds[$alias])) {
            throw new \LogicException(
                sprintf('"%s" alias is already defined for service "%s"', $alias, $handler)
            );
        }

        $this->handlerIds[$alias] = $handler;
    }

    /**
     * Get handler service
     *
     * @param string $alias
     *
     * @return MassActionHandlerInterface
     *
     * @throws \LogicException
     */
    public function getHandler($alias)
    {
        if (!isset($this->handlerIds[$alias])) {
            throw new \LogicException(sprintf('"%s" alias is unknown', $alias));
        }

        return $this->handlerIds[$alias];
    }
}
