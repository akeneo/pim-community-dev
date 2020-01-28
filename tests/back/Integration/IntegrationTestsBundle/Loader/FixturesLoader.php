<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\IntegrationTestsBundle\Loader;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Loader\FixturesLoaderInterface;

/**
 * Override of the CE fixtures loader to add permissions cleaning.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FixturesLoader implements FixturesLoaderInterface
{
    /** @var FixturesLoaderInterface */
    protected $baseFixturesLoader;

    /** @var PermissionCleaner */
    protected $permissionCleaner;

    /**
     * @param FixturesLoaderInterface $baseFixturesLoader
     * @param PermissionCleaner       $permissionCleaner
     */
    public function __construct(FixturesLoaderInterface $baseFixturesLoader, PermissionCleaner $permissionCleaner)
    {
        $this->baseFixturesLoader = $baseFixturesLoader;
        $this->permissionCleaner = $permissionCleaner;
    }

    public function load(Configuration $configuration): void
    {
        $this->baseFixturesLoader->load($configuration);
        $this->permissionCleaner->cleanPermission();
    }
}
