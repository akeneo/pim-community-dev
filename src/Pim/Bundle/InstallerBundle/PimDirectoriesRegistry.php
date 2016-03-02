<?php

namespace Pim\Bundle\InstallerBundle;

/**
 * Contains the list of directories that need to be checked/created during the installation.
 * Example: %archive_dir%, %catalog_storage_dir%...
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimDirectoriesRegistry
{
    /** @var array */
    protected $directories;

    /**
     * @param array $directories
     */
    public function __construct(array $directories)
    {
        $this->directories = $directories;
    }

    /**
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }
}
