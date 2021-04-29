<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Domain\Marketplace\MarketplaceUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MarketplaceUrlController
{
    private MarketplaceUrlGeneratorInterface $marketplaceUrlGenerator;

    public function __construct(MarketplaceUrlGeneratorInterface $marketplaceUrlGenerator)
    {
        $this->marketplaceUrlGenerator = $marketplaceUrlGenerator;
    }

    public function get(Request $request): JsonResponse
    {
        $url = $this->marketplaceUrlGenerator->generateUrl();

        return new JsonResponse($url);
    }
}
