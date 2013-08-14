<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Oro\Bundle\GridBundle\Action\ActionInterface;

interface MassActionInterface
{
    /**
     * Mass action name
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
     * Mass action label
     *
     * @return string|null
     */
    public function getLabel();

    /**
     * Mass action route
     *
     * @return string|null
     */
    public function getRoute();

    /**
     * Action options (route, ACL resource etc.)
     *
     * @return array
     */
    public function getOptions();

    /**
     * Get specific option by name
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name);
}
