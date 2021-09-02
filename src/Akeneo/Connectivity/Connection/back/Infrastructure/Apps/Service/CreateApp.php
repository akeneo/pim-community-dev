<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateAppInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\App;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App as MarketplaceApp;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DbalAppRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateApp implements CreateAppInterface
{
    private DbalAppRepository $repository;

    public function __construct(DbalAppRepository $repository) {
        $this->repository = $repository;
    }

    public function execute(MarketplaceApp $marketplaceApp, array $scopes, string $connectionCode): App
    {
        $app = new App(
            $marketplaceApp->getId(),
            $marketplaceApp->getName(),
            $scopes,
            $connectionCode,
            $marketplaceApp->getLogo(),
            $marketplaceApp->getAuthor(),
            $marketplaceApp->getCategories(),
            $marketplaceApp->isCertified(),
            $marketplaceApp->getPartner()
        );

        $this->repository->create($app);

        return $app;
    }
}
