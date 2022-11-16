<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\SmtpChecker;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;

class SmtpCheckerIntegration extends TestCase
{
    public function test_smtp_check_is_ok_when_there_is_no_error_when_sending_a_mail(): void
    {
        $pingableDsn = getenv('MAILER_DSN');

        $checker = new SmtpChecker(
            self::getContainer()->get('mailer.transport_factory.smtp'),
            $pingableDsn,
            $this->createMock(LoggerInterface::class),
        );
        $status = $checker->status();

        Assert::assertEquals(true, $status->isOk());
    }

    public function test_smtp_check_is_ko_when_there_is_an_error_when_sending_a_mail(): void
    {
        $notPingableDsn = 'smtp://foo.bar';

        $checker = new SmtpChecker(
            self::getContainer()->get('mailer.transport_factory.smtp'),
            $notPingableDsn,
            $this->createMock(LoggerInterface::class),
        );
        $status = $checker->status();

        Assert::assertEquals(false, $status->isOk());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
