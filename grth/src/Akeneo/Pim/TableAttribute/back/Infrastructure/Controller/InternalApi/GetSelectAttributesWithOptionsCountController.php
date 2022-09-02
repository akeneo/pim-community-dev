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

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetOptionsCountAndTranslationByAttribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GetSelectAttributesWithOptionsCountController
{
    private const PAGE_SIZE = 25;

    public function __construct(
        private GetOptionsCountAndTranslationByAttribute $getOptionsCountAndTranslationByAttribute
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $locale = $request->get('locale');
        if (!\is_string($locale)) {
            throw new BadRequestHttpException('The "locale" parameter is missing');
        }

        $limit = (int) $request->get('limit', self::PAGE_SIZE);
        $offset = (int) $request->get('offset', 0);
        $search = $request->get('search', null);

        $selectAttributes = $this->getOptionsCountAndTranslationByAttribute->search(
            $locale,
            $limit,
            $offset,
            $search
        );

        return new JsonResponse($selectAttributes);
    }
}
