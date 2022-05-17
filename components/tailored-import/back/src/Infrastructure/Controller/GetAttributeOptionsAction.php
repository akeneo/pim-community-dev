<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsParameters;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\SearchAttributeOptionsQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GetAttributeOptionsAction
{
    private const LIMIT_DEFAULT = 25;

    public function __construct(
        private SearchAttributeOptionsInterface $searchAttributeOptions,
        private ValidatorInterface $validator,
        private NormalizerInterface $violationNormalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $violations = $this->validator->validate($request, new SearchAttributeOptionsQuery());
        if (0 < $violations->count()) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $searchAttributeOptionsParameters = new SearchAttributeOptionsParameters();
        $searchAttributeOptionsParameters->setIncludeCodes($request->get('include_codes'));
        $searchAttributeOptionsParameters->setExcludeCodes($request->get('exclude_codes'));
        $searchAttributeOptionsParameters->setSearch($request->get('search'));
        $searchAttributeOptionsParameters->setLocale($request->get('locale'));
        $searchAttributeOptionsParameters->setPage($request->get('page'));
        $searchAttributeOptionsParameters->setLimit($request->get('limit', self::LIMIT_DEFAULT));

        $searchAttributeOptionsResult = $this->searchAttributeOptions->search(
            $request->get('attribute_code'),
            $searchAttributeOptionsParameters,
        );

        return new JsonResponse($searchAttributeOptionsResult->normalize());
    }
}
