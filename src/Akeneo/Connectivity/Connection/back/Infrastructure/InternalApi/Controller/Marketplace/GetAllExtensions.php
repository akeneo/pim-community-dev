<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllExtensionsQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllExtensions
{
    private GetAllExtensionsQueryInterface $getAllExtensionsQuery;

    public function __construct(GetAllExtensionsQueryInterface $getAllExtensionsQuery)
    {
        $this->getAllExtensionsQuery = $getAllExtensionsQuery;
    }

    public function __invoke(): Response
    {
        $result = $this->getAllExtensionsQuery->execute();

        return new JsonResponse($result->normalize());
    }
}
