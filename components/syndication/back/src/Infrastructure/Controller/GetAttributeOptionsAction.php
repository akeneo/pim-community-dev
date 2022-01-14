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

namespace Akeneo\Platform\Syndication\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsParameters;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GetAttributeOptionsAction
{
    private const LIMIT_DEFAULT = 25;

    private SearchAttributeOptionsInterface $searchAttributeOptions;

    public function __construct(
        SearchAttributeOptionsInterface $searchAttributeOptions
    ) {
        $this->searchAttributeOptions = $searchAttributeOptions;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $attributeCode = $request->get('attribute_code');
        if (null === $attributeCode) {
            throw new BadRequestHttpException('Missing attribute code');
        }

        $searchAttributeOptionsParameters = new SearchAttributeOptionsParameters();
        $searchAttributeOptionsParameters->setIncludeCodes($request->get('include_codes'));
        $searchAttributeOptionsParameters->setExcludeCodes($request->get('exclude_codes'));
        $searchAttributeOptionsParameters->setSearch($request->get('search'));
        $searchAttributeOptionsParameters->setLocale($request->get('locale'));
        $searchAttributeOptionsParameters->setLimit($request->get('limit', self::LIMIT_DEFAULT));
        $searchAttributeOptionsParameters->setPage($request->get('page'));

        $searchAttributeOptionsResult = $this->searchAttributeOptions->search(
            $attributeCode,
            $searchAttributeOptionsParameters,
        );

        return new JsonResponse($searchAttributeOptionsResult->normalize());
    }
}
