<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WrongCredentialsCombinationController
{
    private WrongCredentialsCombinationRepository $repository;

    public function __construct(WrongCredentialsCombinationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function list(): JsonResponse
    {
        $wrongCombinations = $this->repository->findAll(
            new \DateTimeImmutable('now - 7 day', new \DateTimeZone('UTC'))
        );

        return new JsonResponse($wrongCombinations->normalize());
    }
}
