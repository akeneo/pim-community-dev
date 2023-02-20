<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQueryPagination;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuerySearch;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamiliesWithLabels;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetFamiliesController
{
    private const DEFAULT_PAGE_PAGINATION = 1;
    private const DEFAULT_LIMIT_PAGINATION = 20;

    public function __construct(
        private readonly FindFamiliesWithLabels $findFamiliesWithLabels,
        private readonly UserContext $userContext,
        private readonly SecurityFacadeInterface $securityFacade,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (false === $this->securityFacade->isGranted('pim_enrich_family_index')) {
            throw new AccessDeniedException();
        }

        $families = $this->findFamiliesWithLabels->fromQuery(new FamilyQuery(
            search: new FamilyQuerySearch($request->query->get('search', ''), $this->userContext->getCurrentLocaleCode()),
            pagination: new FamilyQueryPagination(
                (int)$request->query->get('page', (string)self::DEFAULT_PAGE_PAGINATION),
                (int)$request->query->get('limit', (string)self::DEFAULT_LIMIT_PAGINATION),
            ),
            includeCodes: ($request->query->get('codes') ? (array)$request->query->get('codes') : null)
        ));

        $normalizedFamilies = array_map(fn ($family) => $family->normalize(), $families);

        return new JsonResponse($normalizedFamilies);
    }
}
