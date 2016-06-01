<?php

namespace Akeneo\Component\Batch\Step;

/**
 * Implemented by steps or step elements that need to be aware of a resource.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface ResourceAwareInterface
{
    /**
     * @param FilesystemResource $resource
     */
    public function setResource(FilesystemResource $resource);
}
