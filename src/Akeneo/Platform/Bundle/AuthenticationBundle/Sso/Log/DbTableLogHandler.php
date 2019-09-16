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
    /** @var Connection */
    private $connection;

    /** @®ar int */
    private $maxDays = 0;

    /** @var boolean */
    private $initialized = false;

    /** @static string */
    const TABLE_NAME = 'pimee_sso_log';

    public function __construct(
        Connection $connection,
        int $maxDays = 0,
        $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        $this->connection = $connection;
        $this->maxDays = $maxDays;

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record): void
    {
        if (!$this->initialized) {
            $this->initialize();
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

        $this->rotate();
    }

    private function initialize(): void
    {
        $this->connection->exec(
            sprintf(
                'CREATE TABLE IF NOT EXISTS %s' .
                '(time DATETIME, channel VARCHAR(255), level INTEGER, message LONGTEXT, INDEX(time))',
                self::TABLE_NAME
            )
        );

        $this->initialized = true;
    }

    private function rotate(): void
    {
        if (0 === $this->maxDays) {
            return;
        }

        if (!$this->initialized) {
            $this->initialize();
        }

        $expirationDate = new \DateTime(sprintf("%s days ago", $this->maxDays));

        $this->connection->executeQuery(
            sprintf('DELETE FROM %s WHERE time < :expirationTime', self::TABLE_NAME),
            [
                'expirationTime' => $this->connection->convertToDatabaseValue($expirationDate, 'datetime')
            ]
        );
    }
}
