<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Settings\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\IsConnectionsNumberLimitReachedQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsConnectionsNumberLimitReachedAction
{
    public function __construct(private IsConnectionsNumberLimitReachedQueryInterface $isConnectionsNumberLimitReachedQuery)
    {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return new JsonResponse(['limitReached' => $this->isConnectionsNumberLimitReachedQuery->execute()]);
    }
}
