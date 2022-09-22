<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoryChildrenQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryChildrenAction
{
    public function __construct(private GetCategoryChildrenQueryInterface $getCategoryChildrenQuery)
    {
    }

    public function __invoke(Request $request, string $categoryCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $locale = $request->query->get('locale', 'en_US');
        if (!\is_string($locale)) {
            throw new BadRequestHttpException('Locale must be a string.');
        }

        return new JsonResponse($this->getCategoryChildrenQuery->execute($categoryCode, $locale));
    }
}
