<?php

namespace Akeneo\Tool\Component\Versioning\Model;

/**
 * Versionable interface, models  implementing this interface will be versioned
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: use constructor and remove setters to make it immutable
 */
interface VersionableInterface
{
    /**
     * Get id
     *
     * @return int|string
     */
    public function getId();
}
