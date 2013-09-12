<?php

namespace Oro\Bundle\SecurityBundle\Annotation\Loader;

use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;

interface AclAnnotationLoaderInterface
{
    /**
     * Loads ACL annotations
     *
     * @param AclAnnotationStorage $storage
     */
    public function load(AclAnnotationStorage $storage);
}
