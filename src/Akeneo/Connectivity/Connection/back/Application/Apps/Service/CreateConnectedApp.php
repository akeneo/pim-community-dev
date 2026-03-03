<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateConnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App as MarketplaceApp;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateConnectedApp implements CreateConnectedAppInterface
{
    public function __construct(
        private CreateConnectedAppQueryInterface $createConnectedAppQuery,
    ) {
    }

    public function execute(
        MarketplaceApp $marketplaceApp,
        array $scopes,
        string $connectionCode,
        string $userGroupName,
        string $connectionUsername
    ): ConnectedApp {
        $app = new ConnectedApp(
            $marketplaceApp->getId(),
            $marketplaceApp->getName(),
            $scopes,
            $connectionCode,
            $marketplaceApp->getLogo(),
            $marketplaceApp->getAuthor(),
            $userGroupName,
            $connectionUsername,
            $marketplaceApp->getCategories(),
            $marketplaceApp->isCertified(),
            $marketplaceApp->getPartner()
        );

        $this->createConnectedAppQuery->execute($app);

        return $app;
    }
}
