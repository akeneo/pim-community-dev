<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class GetIdentifierAttributesController
{
    // The maximum count of identifier attributes in the PIM
    const MAX_RESULTS = 1;

    public function __construct(
        private FindFlattenAttributesInterface $findFlattenAttributes,
        private SecurityFacadeInterface $securityFacade,
        private UserContext $userContext,
    ) {
    }

    public function __invoke(Request $request): Response {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (!$this->securityFacade->isGranted('pim_enrich_attribute_index')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to list attributes.');
        }

        $attributes = $this->findFlattenAttributes->execute(
            $this->userContext->getCurrentLocaleCode(),
        self::MAX_RESULTS,
            ['pim_catalog_identifier']
        );

        $normalizeAttribute = static function (FlattenAttribute $attribute): array {
            return [
                'code' => $attribute->getCode(),
                'label' => $attribute->getLabel(),
            ];
        };

        return new JsonResponse(array_map(
            $normalizeAttribute,
            $attributes
        ), Response::HTTP_OK);
    }
}
