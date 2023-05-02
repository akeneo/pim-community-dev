<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\OpenIdKeysNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAsymmetricKeysQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetOpenIdPublicKeyAction
{
    public function __construct(private GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $asymmetricKeys = $this->getAsymmetricKeysQuery->execute()->normalize();

            return new JsonResponse([AsymmetricKeys::PUBLIC_KEY => $asymmetricKeys[AsymmetricKeys::PUBLIC_KEY]]);
        } catch (OpenIdKeysNotFoundException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
