<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\GetCategoryTreeRootsQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTreeRootsAction
{
    public function __construct(private GetCategoryTreeRootsQueryInterface $getCategoryTreeRootsQuery)
    {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return new JsonResponse($this->getCategoryTreeRootsQuery->execute());
    }
}
