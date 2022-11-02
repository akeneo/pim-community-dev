<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

class SmtpCheckerSpec extends ObjectBehavior
{
    public function let(TransportFactoryInterface $transportFactory, LoggerInterface $logger): void
    {
        $this->beConstructedWith($transportFactory, 'smtp://null', $logger);
    }

    public function it_checks_if_smtp_is_ok_when_there_is_no_error_when_doing_a_ping(
        TransportFactoryInterface $transportFactory,
        EsmtpTransport $transport,
    ): void {
        $transportFactory->create(Argument::type(Dsn::class))->willReturn($transport);
        $transport->executeCommand("NOOP\r\n", [250])->shouldBeCalled();

        $this->status()->shouldBeLike(ServiceStatus::ok());
    }

    public function it_checks_if_smtp_is_ko_when_there_is_an_error_when_doing_a_ping(
        TransportFactoryInterface $transportFactory,
        EsmtpTransport $transport,
    ): void {
        $transportFactory->create(Argument::type(Dsn::class))->willReturn($transport);
        $transportException = new TransportException('transport error');
        $transport->executeCommand("NOOP\r\n", [250])->willThrow($transportException);

        $this->status()->shouldBeLike(ServiceStatus::notOk('Unable to ping the mailer transport: "transport error".'));
    }
}
