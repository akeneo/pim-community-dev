<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Category\GetCategoriesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Category\GetCategoryTreeRootsQueryInterface;
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
    public function __construct(
        private GetCategoryTreeRootsQueryInterface $getCategoryTreeRootsQuery,
        private GetCategoriesByCodeQueryInterface $getCategoriesByCodeQuery,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $isRoot = $request->query->get('is_root', false);
        $codes = $request->query->get('codes', '');
        $locale = $request->query->get('locale', 'en_US');

        if (!\is_string($locale)) {
            throw new BadRequestHttpException('Locale must be a string.');
        }

        if (!\is_string($codes)) {
            throw new BadRequestHttpException('Codes must be a string.');
        }

        if (!$isRoot && $codes === '') {
            throw new BadRequestHttpException('Either Codes or is_root must be specified');
        }

        if ($isRoot && $codes !== '') {
            throw new BadRequestHttpException('Is_root cannot be used with codes.');
        }

        $categories = $isRoot
            ? $this->getCategoryTreeRootsQuery->execute($locale)
            : $this->getCategoriesByCodeQuery->execute(\explode(',', $codes), $locale);

        return new JsonResponse($categories);
    }
}
