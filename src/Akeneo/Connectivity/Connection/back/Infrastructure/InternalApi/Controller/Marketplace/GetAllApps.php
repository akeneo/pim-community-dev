<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllAppsQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetAllApps
{
    private AppUrlGenerator $appUrlGenerator;
    private GetAllAppsQueryInterface $getAllAppsQuery;
    private MarketplaceAnalyticsGenerator $marketplaceAnalyticsGenerator;
    private UserContext $userContext;

    public function __construct(
        AppUrlGenerator $appUrlGenerator,
        GetAllAppsQueryInterface $getAllAppsQuery,
        MarketplaceAnalyticsGenerator $marketplaceAnalyticsGenerator,
        UserContext $userContext
    ) {
        $this->appUrlGenerator = $appUrlGenerator;
        $this->getAllAppsQuery = $getAllAppsQuery;
        $this->marketplaceAnalyticsGenerator = $marketplaceAnalyticsGenerator;
        $this->userContext = $userContext;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        $result = $this->getAllAppsQuery->execute();

        $username = $this->userContext->getUser()->getUsername();
        $analyticsQueryParameters = $this->marketplaceAnalyticsGenerator->getExtensionQueryParameters($username);
        $result = $result->withAnalytics($analyticsQueryParameters);
        $appQueryParameters = $this->appUrlGenerator->getAppQueryParameters();
        $result = $result->withPimUrlSource($appQueryParameters);

        return new JsonResponse($result->normalize());
    }
}
