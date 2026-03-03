<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\IsCustomAppsNumberLimitReachedQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsCustomAppsNumberLimitReachedAction
{
    public function __construct(private readonly IsCustomAppsNumberLimitReachedQueryInterface $isCustomAppsNumberLimitReachedQuery)
    {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return new JsonResponse($this->isCustomAppsNumberLimitReachedQuery->execute());
    }
}
