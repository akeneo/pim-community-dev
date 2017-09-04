<?php

namespace Akeneo\TestEnterprise\Integration;

use Akeneo\Test\Integration\FixturesLoader as BaseFixturesLoader;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Override of the CE fixtures loader to add permissions cleaning.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FixturesLoader extends BaseFixturesLoader
{
    protected function loadData()
    {
        parent::loadData();

        $permissionCleaner = new PermissionCleaner($this->kernel);
        $permissionCleaner->cleanPermission();
    }
}
