<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Attribute\SearchAttributeOptionsQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAttributeOptionsAction
{
    public function __construct(
        private FindOneAttributeByCodeQueryInterface $findOneAttributeByCodeQuery,
        private SearchAttributeOptionsQueryInterface $searchAttributeOptionsQuery,
    ) {
    }

    public function __invoke(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $attribute = $this->findOneAttributeByCodeQuery->execute($code);

        if (null === $attribute) {
            throw new NotFoundHttpException(\sprintf('$attribute "%s" does not exist.', $code));
        }

        $locale = $request->query->get('locale', 'en_US');
        $search = $request->query->get('search', null);
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);

        if ($page < 1 || $limit < 1) {
            throw new BadRequestHttpException('Page and limit must be positive.');
        }
        if (!\is_string($search) && null !== $search) {
            throw new BadRequestHttpException('Search must be a string or null.');
        }
        if (!\is_string($locale)) {
            throw new BadRequestHttpException('Locale must be a string.');
        }

        $options = $this->searchAttributeOptionsQuery->execute($code, $locale, $search, $page, $limit);

        return new JsonResponse($options);
    }
}
