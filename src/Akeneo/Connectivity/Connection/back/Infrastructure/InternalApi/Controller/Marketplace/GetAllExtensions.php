<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllExtensionsQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllExtensions
{
    private GetAllExtensionsQueryInterface $getAllExtensionsQuery;
    private MarketplaceAnalyticsGenerator $marketplaceAnalyticsGenerator;
    private UserContext $userContext;
    private LoggerInterface $logger;

    public function __construct(
        GetAllExtensionsQueryInterface $getAllExtensionsQuery,
        MarketplaceAnalyticsGenerator $marketplaceAnalyticsGenerator,
        UserContext $userContext,
        LoggerInterface $logger
    ) {
        $this->getAllExtensionsQuery = $getAllExtensionsQuery;
        $this->marketplaceAnalyticsGenerator = $marketplaceAnalyticsGenerator;
        $this->userContext = $userContext;
        $this->logger = $logger;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        try {
            $result = $this->getAllExtensionsQuery->execute();
        } catch (\Exception $e) {
            $this->logger->error(sprintf('unable to retrieve the list of extensions, got error "%s"', $e->getMessage()));

            return new JsonResponse(null, 500);
        }

        $username = $this->userContext->getUser()->getUsername();
        $analyticsQueryParameters = $this->marketplaceAnalyticsGenerator->getExtensionQueryParameters($username);
        $result = $result->withAnalytics($analyticsQueryParameters);

        return new JsonResponse($result->normalize());
    }
}
