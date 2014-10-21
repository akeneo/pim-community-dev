<?php

namespace Pim\Component\Resource\Domain;

/**
 * Resource interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ResourceInterface
{
    /**
     * Determines if a resource is new or not.
     *
     * @return bool
     */
    public function isNew();
}
