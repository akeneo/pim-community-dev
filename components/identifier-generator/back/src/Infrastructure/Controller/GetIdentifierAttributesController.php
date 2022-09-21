<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetIdentifierAttributesController
{
    public function __construct(
        private FindFlattenAttributesInterface $findFlattenAttributes
    ) {
    }

    public function __invoke(Request $request): Response {
//        if (!$request->isXmlHttpRequest()) {
//            return new RedirectResponse('/');
//        }

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
