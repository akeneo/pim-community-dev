<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\SmtpChecker;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Swift_SmtpTransport;
use Swift_Transport;

/**
 * Check if the SMTP probe works as expected.
 *
 * In production, the probe will be configured with the service "swiftmailer.mailer.default.transport.real".
 *
 * Indeed, when using $mailer->send(), mails are actually put in a queue. The queue is flushed with the events
 * KernelEvents::TERMINATE. The real service called to send messages (as we use a spool) is
 * "swiftmailer.mailer.default.transport.real".
 *
 * See https://github.com/symfony/swiftmailer-bundle/blob/v3.2.6/EventListener/EmailSenderListener.php#L61
 */
class SmtpCheckerIntegration extends TestCase
{
    public function test_smtp_check_is_ok_when_there_is_no_error_when_sending_a_mail()
    {
        $checker = new SmtpChecker($this->getPingableMailerTransport());
        $status = $checker->status();

        Assert::assertEquals(true, $status->isOk());
    }

    public function test_smtp_check_is_ko_when_there_is_an_error_when_sending_a_mail()
    {
        $checker = new SmtpChecker($this->getNotPingableMailerTransport());
        $status = $checker->status();

        Assert::assertEquals(false, $status->isOk());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * Configure a wrong transport that will always fail to ping.
     *
     * @return Swift_Transport
     */
    private function getNotPingableMailerTransport(): Swift_Transport
    {
        return new Swift_SmtpTransport('foobar.example.com', 999);
    }

    /**
     * This transport always returns "true" to ping.
     *
     * @return Swift_Transport
     */
    private function getPingableMailerTransport(): Swift_Transport
    {
        return $this->get('swiftmailer.mailer.default.transport.spool');
    }
}
