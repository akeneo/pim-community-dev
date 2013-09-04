<?php

namespace Oro\Bundle\ConfigBundle\Provider;

use Symfony\Component\Form\FormInterface;

interface ProviderInterface
{
    /**
     * Returns specified tree
     *
     * @return array
     */
    public function getTree();

    /**
     * Builds form for specified tree group
     *
     * @param string $groupName
     * @return FormInterface
     */
    public function getForm($groupName);

    /**
     * Retrieve slice of specified tree in point of subtree
     *
     * @param string $subTreeName
     * @return mixed
     */
    public function getSubTree($subTreeName);
}
