<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllAppsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Psr\Log\LoggerInterface;
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
    private LoggerInterface $logger;
    private FeatureFlag $activateFeatureFlag;

    public function __construct(
        AppUrlGenerator $appUrlGenerator,
        GetAllAppsQueryInterface $getAllAppsQuery,
        MarketplaceAnalyticsGenerator $marketplaceAnalyticsGenerator,
        UserContext $userContext,
        LoggerInterface $logger,
        FeatureFlag $activateFeatureFlag
    ) {
        $this->appUrlGenerator = $appUrlGenerator;
        $this->getAllAppsQuery = $getAllAppsQuery;
        $this->marketplaceAnalyticsGenerator = $marketplaceAnalyticsGenerator;
        $this->userContext = $userContext;
        $this->logger = $logger;
        $this->activateFeatureFlag = $activateFeatureFlag;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->activateFeatureFlag->isEnabled()) {
            return new JsonResponse(GetAllAppsResult::create(0, [])->normalize());
        }

        try {
            $result = $this->getAllAppsQuery->execute();
        } catch (\Exception $e) {
            $this->logger->error(sprintf('unable to retrieve the list of apps, got error "%s"', $e->getMessage()));

            return new Response('unable to retrieve the list of apps, please check the logs.', 204);
        }

        $username = $this->userContext->getUser()->getUsername();
        $analyticsQueryParameters = $this->marketplaceAnalyticsGenerator->getExtensionQueryParameters($username);
        $result = $result->withAnalytics($analyticsQueryParameters);
        $appQueryParameters = $this->appUrlGenerator->getAppQueryParameters();
        $result = $result->withPimUrlSource($appQueryParameters);

        return new JsonResponse($result->normalize());
    }
}
