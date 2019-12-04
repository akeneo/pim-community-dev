<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Log;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\DbTableLogHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\EnabledConfigurationRepository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Test\Integration\TestCase;
use Monolog\Logger;

final class MysqlCheckerIntegration extends TestCase
{
    public function test_mysql_is_ok_when_mysql_when_you_can_request_a_pim_table_without_error(): void
    {

    }

    public function test_mysql_is_ok_when_mysql_when_you_cant_request_a_pim_table_without_error(): void
    {

    }

    protected function getConfiguration()
    {
        return null;
    }
}
