<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\MarketplaceUrlGeneratorInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetWebMarketplaceUrl
{
    private MarketplaceUrlGeneratorInterface $marketplaceUrlGenerator;
    private UserContext $userContext;

    public function __construct(
        MarketplaceUrlGeneratorInterface $marketplaceUrlGenerator,
        UserContext $userContext
    ) {
        $this->marketplaceUrlGenerator = $marketplaceUrlGenerator;
        $this->userContext = $userContext;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $username = $this->userContext->getUser()->getUsername();
        $url = $this->marketplaceUrlGenerator->generateUrl($username);

        return new JsonResponse($url);
    }
}
