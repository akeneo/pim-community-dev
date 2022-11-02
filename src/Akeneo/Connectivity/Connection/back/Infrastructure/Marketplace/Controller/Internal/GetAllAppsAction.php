<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Controller\Internal;

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
final class GetAllAppsAction
{
    public function __construct(
        private AppUrlGenerator $appUrlGenerator,
        private GetAllAppsQueryInterface $getAllAppsQuery,
        private MarketplaceAnalyticsGenerator $marketplaceAnalyticsGenerator,
        private UserContext $userContext,
        private LoggerInterface $logger,
        private FeatureFlag $activateFeatureFlag,
    ) {
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
            $this->logger->error(\sprintf('unable to retrieve the list of apps, got error "%s"', $e->getMessage()));

            if (Response::HTTP_BAD_REQUEST === $e->getCode()) {
                return new JsonResponse(GetAllAppsResult::create(0, [])->normalize());
            }

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $username = $this->userContext->getUser()->getUserIdentifier();
        $analyticsQueryParameters = $this->marketplaceAnalyticsGenerator->getExtensionQueryParameters($username);
        $result = $result->withAnalytics($analyticsQueryParameters);
        $appQueryParameters = $this->appUrlGenerator->getAppQueryParameters();
        $result = $result->withPimUrlSource($appQueryParameters);

        return new JsonResponse($result->normalize());
    }
}
