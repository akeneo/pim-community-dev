<?php

namespace Akeneo\TestEnterprise\Integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\DatabaseSchemaHandler;
use Akeneo\Test\Integration\TestCase as BaseTestCase;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getFixturesLoader(Configuration $configuration, DatabaseSchemaHandler $databaseSchemaHandler)
    {
        return new FixturesLoader(static::$kernel, $configuration, $databaseSchemaHandler);
    }
}
