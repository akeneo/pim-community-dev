<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAsymmetricKeysQueryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetJwtPublicKeyAction
{
    public function __construct(private GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery)
    {
    }

    public function __invoke(Request $request): Response
    {
        ['public_key' => $publicKey] = $this->getAsymmetricKeysQuery->execute();

        return new Response($publicKey, Response::HTTP_OK);
    }
}
