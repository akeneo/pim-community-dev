<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class GetIdentifierAttributesController
{
    private const MAX_RESULTS = 20;

    public function __construct(
        private readonly FindFlattenAttributesInterface $findFlattenAttributes,
        private readonly UserContext $userContext,
        private readonly SecurityFacadeInterface $security,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }
        if (!$this->security->isGranted('pim_identifier_generator_view')
            && !$this->security->isGranted('pim_identifier_generator_manage')
        ) {
            throw new AccessDeniedException();
        }

        $attributes = $this->findFlattenAttributes->execute(
            $this->userContext->getCurrentLocaleCode(),
            self::MAX_RESULTS,
            [AttributeTypes::IDENTIFIER]
        );

        $normalizeAttribute = static fn (FlattenAttribute $attribute): array =>[
            'code' => $attribute->getCode(),
            'label' => $attribute->getLabel(),
        ];

        return new JsonResponse(\array_map(
            $normalizeAttribute,
            $attributes
        ), Response::HTTP_OK);
    }
}
