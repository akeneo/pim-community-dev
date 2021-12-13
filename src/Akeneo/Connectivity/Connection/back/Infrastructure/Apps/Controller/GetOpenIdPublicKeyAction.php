<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAsymmetricKeysQueryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetOpenIdPublicKeyAction
{
    private GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery;

    public function __construct(GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery)
    {
        $this->getAsymmetricKeysQuery = $getAsymmetricKeysQuery;
    }

    public function __invoke(Request $request): Response
    {
        $asymmetricKeys = $this->getAsymmetricKeysQuery->execute()->normalize();

        return new Response($asymmetricKeys[AsymmetricKeys::PUBLIC_KEY], Response::HTTP_OK);
    }
}
