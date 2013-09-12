<?php

namespace Oro\Bundle\SecurityBundle\Annotation\Loader;

use Symfony\Component\HttpKernel\KernelInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;

abstract class AbstractLoader
{
    /**
     * @var AclExtensionSelector
     */
    protected $extensionSelector;

    /**
     * Constructor
     *
     * @param AclExtensionSelector $extensionSelector
     */
    protected function __construct(AclExtensionSelector $extensionSelector)
    {
        $this->extensionSelector = $extensionSelector;
    }

    /**
     * Performs some additional modifications (if needed) of ACL annotation objects
     *
     * @param AclAnnotation $annotation
     */
    protected function postLoadAnnotation(AclAnnotation $annotation)
    {
        // set default permission if it is not specified
        if ($annotation->getPermission() === '') {
            foreach ($this->extensionSelector->all() as $extension) {
                if ($annotation->getType() === $extension->getExtensionKey()) {
                    $annotation->setPermission($extension->getDefaultPermission());
                    break;
                }
            }
        }
    }
}
