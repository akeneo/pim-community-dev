<?php

namespace Oro\Bundle\GridBundle\Action;

interface ActionInterface
{
    /**
     * Action types
     */
    const TYPE_REDIRECT = 'oro_grid_action_redirect';
    const TYPE_DELETE   = 'oro_grid_action_delete';

    /**
     * Filter name
     *
     * @return string
     */
    public function getName();

    /**
     * Action type
     *
     * @return string
     */
    public function getType();

    /**
     * ACL resource name
     *
     * @return string|null
     */
    public function getAclResource();

    /**
     * Action options (route, ACL resource etc.)
     *
     * @return array
     */
    public function getOptions();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @param string $aclResource
     */
    public function setAclResource($aclResource);

    /**
     * @param array $options
     */
    public function setOptions(array $options);
}
