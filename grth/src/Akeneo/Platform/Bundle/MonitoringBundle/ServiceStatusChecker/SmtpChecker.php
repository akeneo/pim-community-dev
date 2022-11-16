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
use Symfony\Component\Mailer\Exception\LogicException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\Stream\AbstractStream;

final class SmtpChecker
{
    public function __construct(
        private EsmtpTransportFactory $transportFactory,
        private string $mailerDsn,
        private LoggerInterface $logger,
    ) {
    }

    public function status(): ServiceStatus
    {
        try {
            $dsn = Dsn::fromString($this->mailerDsn);
            /**
             * @var EsmtpTransport $transport
             */
            $transport = $this->transportFactory->create($dsn);
            //TODO: Replace this workaround by $transport->start() when we'll be on symfony/mailer:^6.1 (https://github.com/symfony/symfony/pull/45388)
            $this->startConnection($transport);
        } catch (\Throwable $e) {
            $this->logger->error("Smtp ServiceCheck error", ['exception' => $e]);
            return ServiceStatus::notOk(sprintf('Unable to ping the mailer transport: "%s".', $e->getMessage()));
        }

        return ServiceStatus::ok();
    }

    /**
     * TODO: Remove this function when we'll be on symfony/mailer:^6.1 (https://github.com/symfony/symfony/pull/45388)
     */
    private function startConnection(EsmtpTransport $transport)
    {
        $transport->getStream()->initialize();
        $this->assertResponseCode($this->getFullResponse($transport->getStream()), [220]);
        $transport->executeCommand("NOOP\r\n", [250]);
    }

    /**
     * TODO: Remove this function when we'll be on symfony/mailer:^6.1 (https://github.com/symfony/symfony/pull/45388)
     */
    private function getFullResponse(AbstractStream $stream): string
    {
        $response = '';
        do {
            $line = $stream->readLine();
            $response .= $line;
        } while ($line && isset($line[3]) && ' ' !== $line[3]);

        return $response;
    }

    /**
     * TODO: Remove this function when we'll be on symfony/mailer:^6.1 (https://github.com/symfony/symfony/pull/45388)
     */
    private function assertResponseCode(string $response, array $codes): void
    {
        if (!$codes) {
            throw new LogicException('You must set the expected response code.');
        }

        [$code] = sscanf($response, '%3d');
        $valid = \in_array($code, $codes);

        if (!$valid || !$response) {
            $codeStr = $code ? sprintf('code "%s"', $code) : 'empty code';
            $responseStr = $response ? sprintf(', with message "%s"', trim($response)) : '';

            throw new TransportException(sprintf('Expected response code "%s" but got ', implode('/', $codes)).$codeStr.$responseStr.'.', $code ?: 0);
        }
    }
}
