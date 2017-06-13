<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration;

use Akeneo\TestEnterprise\Integration\PermissionCleaner;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase as BaseApiTestCase;

abstract class ApiTestCase extends BaseApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $permissionCleaner = new PermissionCleaner(static::$kernel);
        $permissionCleaner->cleanPermission();
    }
}
