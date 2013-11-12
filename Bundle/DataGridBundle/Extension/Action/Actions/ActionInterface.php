<?php

namespace Oro\Bundle\DataGridBundle\Extension\Action\Actions;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

interface ActionInterface
{
    const ACL_KEY = 'acl_resource';

    /**
     * Filter name
     *
     * @return string
     */
    public function getName();

    /**
     * ACL resource name
     *
     * @return string|null
     */
    public function getAclResource();

    /**
     * Action options (route, ACL resource etc.)
     *
     * @return ActionConfiguration
     */
    public function getOptions();

    /**
     * Set action options
     *
     * @param ActionConfiguration $options
     *
     * @return $this
     */
    public function setOptions(ActionConfiguration $options);
}
