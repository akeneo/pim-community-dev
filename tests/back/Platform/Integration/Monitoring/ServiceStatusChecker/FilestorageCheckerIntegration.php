<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Authentication\Sso\Log;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\DbTableLogHandler;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\EnabledConfigurationRepository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Test\Integration\TestCase;
use Monolog\Logger;

final class FileStorageCheckerIntegration extends TestCase
{
    public function test_filestorage_is_ok_when_when_you_can_write_a_file_in_each_filestorage(): void
    {

    }

    public function test_filestorage_is_ko_when_when_you_cant_write_a_file_in_at_least_one_of_the_filestorage(): void
    {

    }

    protected function getConfiguration()
    {
        return null;
    }
}
