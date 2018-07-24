<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Factory;

use Akeneo\Tool\Component\Versioning\Model\Version;

/**
 * Version factory
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionFactory
{
    /** @var string */
    protected $versionClass;

    /**
     * @param string $versionClass
     */
    public function __construct($versionClass)
    {
        $this->versionClass = $versionClass;
    }

    /**
     * Create a version
     *
     * @param  string  $resourceName
     * @param  mixed   $resourceId
     * @param  string  $author
     * @param  mixed   $context
     *
     * @return Version
     */
    public function create($resourceName, $resourceId, $author, $context = null)
    {
        return new $this->versionClass($resourceName, $resourceId, $author, $context);
    }
}
