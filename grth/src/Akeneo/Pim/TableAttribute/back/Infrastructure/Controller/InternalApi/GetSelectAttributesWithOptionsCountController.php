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
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetSelectAttributesWithOptionsCountController
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private AttributeOptionRepositoryInterface $attributeOptionRepository
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $simpleSelects = $this->attributeRepository->getAttributeCodesByType(AttributeTypes::OPTION_SIMPLE_SELECT);
        $multiSelects = $this->attributeRepository->getAttributeCodesByType(AttributeTypes::OPTION_MULTI_SELECT);

        $options = [];
        foreach (\array_merge($simpleSelects, $multiSelects) as $selectAttributeCode) {
            $attribute = $this->attributeRepository->findOneByCode($selectAttributeCode);
            $optionsCount = $this->attributeOptionRepository->count(['attribute' => $attribute->getId()]);

            $labels = [];
            foreach($attribute->getTranslations()->toArray() as $translation) {
                $labels[$translation->getLocale()] = $translation->getLabel();
            }

            $options[$attribute->getCode()] = [
                "options_count" => $optionsCount,
                "labels" => $labels,
            ];
        }

        return new JsonResponse($options);
    }
}
