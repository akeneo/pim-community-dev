<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Family\GetFamiliesByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Family\SearchFamilyQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetFamiliesAction
{
    public function __construct(
        private SearchFamilyQueryInterface $searchFamilyQuery,
        private GetFamiliesByCodeQueryInterface $getFamiliesByCodeQuery,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $search = $request->query->get('search', null);
        $codes = $request->query->get('codes', null);
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);

        if ($page < 1 || $limit < 1) {
            throw new BadRequestHttpException('Page and limit must be positive.');
        }
        if (!\is_string($search) && null !== $search) {
            throw new BadRequestHttpException('Search must be a string or null.');
        }
        if (!\is_string($codes) && null !== $codes) {
            throw new BadRequestHttpException('Codes must be a string or null.');
        }

        $families = $this->getFamilies($search, $codes, $page, $limit);

        return new JsonResponse($families);
    }

    /**
     * @return array<array{code: string, label: string}>
     */
    private function getFamilies(?string $search, ?string $codes, int $page, int $limit): array
    {
        if (\is_string($codes) && \strlen(\trim($codes)) > 0) {
            return $this->getFamiliesByCodeQuery->execute(\explode(',', $codes), $page, $limit);
        }

        return $this->searchFamilyQuery->execute($search, $page, $limit);
    }
}
