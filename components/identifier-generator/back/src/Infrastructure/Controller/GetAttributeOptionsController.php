<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeOption\FindAttributeOptions;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAttributeOptionsController
{
    public function __construct(
        private readonly FindAttributeOptions $findAttributeOptions,
        private readonly SecurityFacadeInterface $securityFacade,
    ) {
    }

    public function __invoke(Request $request, string $attributeCode): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!(
            $this->securityFacade->isGranted('pim_enrich_attribute_index')
            && (
                $this->securityFacade->isGranted('pim_identifier_generator_manage')
                || $this->securityFacade->isGranted('pim_identifier_generator_view')
            )
        )) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(
            $this->findAttributeOptions->search(
                attributeCode: $attributeCode,
                search: $request->query->get('search', ''),
                page: \intval($request->query->get('page', '1')),
                limit: \intval($request->query->get('limit', '20')),
                includeCodes: $request->query->get('codes') ? (array) $request->query->get('codes') : null,
            )
        );
    }
}
