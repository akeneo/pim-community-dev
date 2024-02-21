<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * TODO: EndToEnd
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ListWrongCredentialsCombinationsAction
{
    public function __construct(
        private WrongCredentialsCombinationRepositoryInterface $repository,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $wrongCombinations = $this->repository->findAll(
            new \DateTimeImmutable('now - 7 day', new \DateTimeZone('UTC'))
        );

        return new JsonResponse($wrongCombinations->normalize());
    }
}
