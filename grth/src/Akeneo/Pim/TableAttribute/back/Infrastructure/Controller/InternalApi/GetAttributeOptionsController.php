<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Controller\InternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetAttributeOptions;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeOptionsController
{
    public function __construct(
        private GetAttributes $getAttributes,
        private GetAttributeOptions $getAttributeOptions
    ) {
    }

    public function __invoke(
        Request $request,
        string $selectAttributeCode
    ): Response {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $selectAttribute = $this->getAttributes->forCode($selectAttributeCode);

        if (null === $selectAttribute) {
            return new JsonResponse([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => sprintf('The "%s" attribute cannot be found', $selectAttributeCode),
            ], Response::HTTP_NOT_FOUND);
        }

        if (!\in_array($selectAttribute->type(), [AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::OPTION_MULTI_SELECT])) {
            return new JsonResponse([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => sprintf('The "%s" attribute is not a simple or multi select attribute', $selectAttributeCode),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $options = $this->getAttributeOptions->forAttributeCode($selectAttributeCode);

        $normalizedOptions = [];
        foreach ($options as $option) {
            $normalizedOptions[] = $option->normalize();
            if (\count($normalizedOptions) >= SelectOptionCollection::MAX_OPTIONS) {
                break;
            }
        }

        return new JsonResponse($normalizedOptions);
    }
}
