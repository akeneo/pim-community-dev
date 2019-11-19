<?php
declare(strict_types=1);

/*
 * this file is part of the akeneo pim enterprise edition.
 *
 * (c) 2014 akeneo sas (http://www.akeneo.com)
 *
 * for the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */
namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Doctrine\DBAL\Connection;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * This Monolog handler logs message into a table in the current database.
 * The table is created automatically if it does not exists.
 *
 * The handler cleans up log entries older than the specified number of
 * days. 0 means no limit.
 */
final class DbTableLogHandler extends AbstractProcessingHandler
{
    private const CONFIGURATION_CODE = 'authentication_sso';

    /** @var Repository */
    private $configRepository;

    /** @var Connection */
    private $connection;

    /** @var bool */
    private $ssoEnabled;

    /** @®ar int */
    private $maxDays = 0;

    /** @static string */
    const TABLE_NAME = 'pimee_sso_log';

    public function __construct(
        Repository $configRepository,
        Connection $connection,
        int $maxDays = 0,
        $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        $this->configRepository = $configRepository;
        $this->connection = $connection;
        $this->maxDays = $maxDays;

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record): void
    {
        if (!$this->isSSOEnabled()) {
            return;
        }

        $datetime = $record['datetime'];

        $statement = $this->connection->prepare(
            sprintf(
                'INSERT INTO %s (time, channel, level, message) VALUES (:time, :channel, :level, :message)',
                self::TABLE_NAME
            )
        );

        $statement->execute(array(
            'time' => $this->connection->convertToDatabaseValue($datetime, 'datetime'),
            'channel' => $record['channel'],
            'level' => $record['level'],
            'message' => $record['formatted']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        parent::close();

        if ($this->isSSOEnabled()) {
            $this->rotate();
        }
    }

    private function rotate(): void
    {
        if (0 === $this->maxDays) {
            return;
        }

        $expirationDate = new \DateTime(sprintf("%s days ago", $this->maxDays));

        $this->connection->executeQuery(
            sprintf('DELETE FROM %s WHERE time < :expirationTime', self::TABLE_NAME),
            [
                'expirationTime' => $this->connection->convertToDatabaseValue($expirationDate, 'datetime')
            ]
        );
    }

    private function isSSOEnabled(): bool
    {
        if (null === $this->ssoEnabled) {
            try {
                $config = $this->configRepository->find(self::CONFIGURATION_CODE);
                $this->ssoEnabled = $config->isEnabled();
            } catch (ConfigurationNotFound $e) {
                $this->ssoEnabled = false;
            }
        }

        return $this->ssoEnabled;
    }
}
