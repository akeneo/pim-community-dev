<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

final class SmtpChecker
{
    public function __construct(
        private TransportFactoryInterface $transportFactory,
        private string $mailerDsn,
        private LoggerInterface $logger,
    ) {
    }

    public function status(): ServiceStatus
    {
        try {
            $transport = $this->transportFactory->create(Dsn::fromString($this->mailerDsn));
            $transport->executeCommand("NOOP\r\n", [250]);
        } catch (\Throwable $e) {
            $this->logger->error("Smtp ServiceCheck error", ['exception' => $e]);
            return ServiceStatus::notOk(sprintf('Unable to ping the mailer transport: "%s".', $e->getMessage()));
        }

        return ServiceStatus::ok();
    }
}
