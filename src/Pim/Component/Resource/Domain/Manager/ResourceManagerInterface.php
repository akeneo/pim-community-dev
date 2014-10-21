<?php


namespace Pim\Component\Resource\Domain\Manager;

use Pim\Component\Resource\Domain\ResourceInterface;

/**
 * Resource manager interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ResourceManagerInterface
{
    /**
     * Saves (creates or updates) a resource.
     *
     * @param ResourceInterface $resource
     */
    public function save(ResourceInterface $resource);

    /**
     * Deletes a resource.
     *
     * @param ResourceInterface $resource
     */
    public function delete(ResourceInterface $resource);
}
