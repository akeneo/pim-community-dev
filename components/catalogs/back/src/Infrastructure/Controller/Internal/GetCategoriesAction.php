<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\GetCategoriesByCodeQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoriesAction
{
    public function __construct(private GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery)
    {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $codes = $request->query->get('codes', '');
        if (!\is_string($codes)) {
            throw new BadRequestHttpException('Codes must be a string or null.');
        }

        return new JsonResponse($this->getCategoriesByCodeQuery->execute(\explode(',', $codes)));
    }
}
