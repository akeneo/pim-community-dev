<?php

namespace Oro\Bundle\GridBundle\Action;

interface ActionFactoryInterface
{
    /**
     * @param string $name
     * @param string $type
     * @param string|null $aclResource
     * @param array  $options
     *
     * @return ActionInterface
     */
    public function create($name, $type, $aclResource = null, array $options = array());
}
