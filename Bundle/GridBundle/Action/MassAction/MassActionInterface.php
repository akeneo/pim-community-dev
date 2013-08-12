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
     * Mass action field name
     *
     * @return string
     */
    public function getFieldName();

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
     * Action options (route, ACL resource etc.)
     *
     * @return array
     */
    public function getOptions();
}
