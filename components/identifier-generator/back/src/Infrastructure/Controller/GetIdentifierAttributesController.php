<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Oro\Bundle\SecurityBundle\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class GetIdentifierAttributesController
{
    public function __construct(
        private FindFlattenAttributesInterface $findFlattenAttributes,
        private SecurityFacadeInterface $securityFacade,
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
            'en_US',
        20,
            ['pim_catalog_identifier']
        );

        $normalizeAttribute = static function (FlattenAttribute $attribute): array {
            return [
                'code' => $attribute->getCode(),
                'labels' => [
                    'en_US' => $attribute->getLabel()
                ]
            ];
        };

        return new JsonResponse(array_map(
            $normalizeAttribute,
            $attributes
        ), Response::HTTP_OK);
    }
}
