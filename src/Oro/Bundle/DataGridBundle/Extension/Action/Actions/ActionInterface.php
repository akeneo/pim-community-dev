<?php

namespace Oro\Bundle\DataGridBundle\Extension\Action\Actions;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

interface ActionInterface
{
    const ACL_KEY = 'acl_resource';

    /**
     * Filter name
     */
    public function getName(): string;

    /**
     * ACL resource name
     *
     * @return string|null
     */
    public function getAclResource(): ?string;

    /**
     * Action options (route, ACL resource etc.)
     */
    public function getOptions(): \Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;

    /**
     * Set action options
     *
     * @param ActionConfiguration $options
     *
     * @return $this
     */
    public function setOptions(ActionConfiguration $options): self;
}
