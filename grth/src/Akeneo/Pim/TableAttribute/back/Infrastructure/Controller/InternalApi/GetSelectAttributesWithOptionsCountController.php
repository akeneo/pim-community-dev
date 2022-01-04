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
    private const PAGE_SIZE = 25;

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

        $limit = (int) $request->get('limit', self::PAGE_SIZE);
        $page = (int) $request->get('page', 1);
        $offset = \abs($page - 1) * $limit;

        $selectAttributes = $this->attributeRepository->findBy(
            [
                'type' => [
                    AttributeTypes::OPTION_SIMPLE_SELECT,
                    AttributeTypes::OPTION_MULTI_SELECT,
                ],
            ],
            ['code' => 'ASC'],
            $limit,
            $offset
        );

        $options = [];
        foreach ($selectAttributes as $selectAttribute) {
            $optionsCount = $this->attributeOptionRepository->count(['attribute' => $selectAttribute->getId()]);

            $labels = [];
            foreach ($selectAttribute->getTranslations() as $translation) {
                $labels[$translation->getLocale()] = $translation->getLabel();
            }

            $options[$selectAttribute->getCode()] = [
                'options_count' => $optionsCount,
                'labels' => $labels,
            ];
        }

        return new JsonResponse($options);
    }
}
