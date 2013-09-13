<?php

namespace Oro\Bundle\SecurityBundle\Annotation\Loader;


use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;

abstract class AbstractLoader
{
    /**
     * @var array
     */
    protected $bundleDirectories;

    /**
     * @var ServiceLink
     */
    protected $extensionSelectorLink;

    /**
     * Constructor
     *
     * @param ServiceLink $extensionSelectorLink
     */
    protected function __construct(ServiceLink $extensionSelectorLink)
    {
        $this->extensionSelectorLink = $extensionSelectorLink;
    }

    public function setBundles($bundles)
    {
        $directories = array();
        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $directories[] = dirname($reflection->getFilename());
        }
        $this->bundleDirectories = $directories;
    }

    /**
     * Performs some additional modifications (if needed) of ACL annotation objects
     *
     * @param AclAnnotation $annotation
     */
    protected function postLoadAnnotation(AclAnnotation $annotation)
    {
        $extensionSelector = $this->extensionSelectorLink->getService();
        // set default permission if it is not specified
        if ($annotation->getPermission() === '') {
            foreach ($extensionSelector->all() as $extension) {
                if ($annotation->getType() === $extension->getExtensionKey()) {
                    $annotation->setPermission($extension->getDefaultPermission());
                    break;
                }
            }
        }
    }
}
