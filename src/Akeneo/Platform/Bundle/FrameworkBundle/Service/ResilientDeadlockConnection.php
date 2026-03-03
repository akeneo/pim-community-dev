<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Result;
use Psr\Log\LoggerInterface;

/**
 * Unfortunately is not possible to decorate DbalConnection because it does not have its proper interface.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResilientDeadlockConnection
{
    private const MAX_RETRY = 5;
    private const DELAY_BETWEEN_RETRY_IN_MICROSECONDS = 300000;

    /**
     * This delay is useful to not restart exactly at the same time concurrent processes locked by the same deadlock,
     * in order to mitigate the probability to have a new deadlock again.
     * The maximum delay is increased in function of the number of retries to minimize the probability of the deadlock to occur,
     * at the cost of increasing the latency
     */
    private const MINIMUM_RANDOM_DELAY_IN_MICROSECONDS = 50000;
    private const MAXIMUM_RANDOM_DELAY_IN_MICROSECONDS = 100000;

    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws DeadlockException
     * @throws Exception
     */
    public function executeQuery(string $sql, array $params = [], $types = [], ?string $cause = null): Result
    {
        $retry = 0;
        while (true) {
            try {
                return $this->connection->executeQuery($sql, $params, $types);
            } catch (DeadlockException $e) {
                $retry += 1;

                if (self::MAX_RETRY === $retry) {
                    $this->logger->warning(sprintf('Deadlock occurred during the execution of "%s", despite several attempts', $cause));
                    throw $e;
                }

                $this->logger->warning(sprintf('Deadlock occurred during the execution of "%s", %d/%d retry', $cause, $retry, self::MAX_RETRY - 1));
                usleep(self::DELAY_BETWEEN_RETRY_IN_MICROSECONDS + rand(self::MINIMUM_RANDOM_DELAY_IN_MICROSECONDS, $retry * self::MAXIMUM_RANDOM_DELAY_IN_MICROSECONDS));
            }
        }
    }
}
