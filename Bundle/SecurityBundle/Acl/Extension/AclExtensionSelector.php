<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

class AclExtensionSelector
{
    /**
     * @var AclExtensionInterface[]
     */
    protected $extensions = array();

    /**
     * @var AclExtensionInterface
     */
    protected $nullExtension = null;

    public function addAclExtension(AclExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    public function select($object)
    {
        foreach ($this->extensions as $extension) {
            if ($extension->supportsObject($object)) {
                return $extension;
            }
        }

        if ($this->nullExtension === null) {
            $this->nullExtension = new NullAclExtension();
        }
        return $this->nullExtension;
    }

    public function all()
    {
        return $this->extensions;
    }
}
