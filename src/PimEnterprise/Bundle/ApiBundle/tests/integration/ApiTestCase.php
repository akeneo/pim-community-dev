<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\DatabaseSchemaHandler;
use Akeneo\TestEnterprise\Integration\FixturesLoader;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase as BaseApiTestCase;

abstract class ApiTestCase extends BaseApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getFixturesLoader(Configuration $configuration, DatabaseSchemaHandler $databaseSchemaHandler)
    {
        return new FixturesLoader(static::$kernel, $configuration, $databaseSchemaHandler);
    }
}
