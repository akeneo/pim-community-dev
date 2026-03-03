<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\Handler;

use Akeneo\Tool\Bundle\ApiBundle\Doctrine\DBAL\DeleteExpiredAccessTokenQuery;
use Akeneo\Tool\Bundle\ApiBundle\Doctrine\DBAL\DeleteExpiredRefreshTokenQuery;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteExpiredTokensHandler
{
    public function __construct(
        private readonly DeleteExpiredAccessTokenQuery $deleteExpiredAccessTokenQuery,
        private readonly DeleteExpiredRefreshTokenQuery $deleteExpiredRefreshTokenQuery,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(): void
    {
        $this->logger->notice('Start expired tokens removal');

        $this->deleteExpiredAccessTokenQuery->execute();

        $this->logger->notice('Expired access tokens removed');

        $this->deleteExpiredRefreshTokenQuery->execute();

        $this->logger->notice('End expired tokens removal');
    }
}
