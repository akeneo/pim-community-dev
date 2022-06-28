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

use Akeneo\Platform\TailoredImport\Application\SearchAttributes\SearchAttributesHandler;
use Akeneo\Platform\TailoredImport\Application\SearchAttributes\SearchAttributesQuery;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\SearchAttributesQuery as SearchAttributesQueryConstraint;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SearchAttributesAction
{
    public function __construct(
        private SearchAttributesHandler $searchAttributesHandler,
        private ValidatorInterface $validator,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $violations = $this->validator->validate($request, new SearchAttributesQueryConstraint());
        if (0 < $violations->count()) {
            return new JsonResponse($this->normalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $searchAttributesQuery = new SearchAttributesQuery();
        $searchAttributesQuery->search = $request->get('search');
        $searchAttributesQuery->attributeCodes = $request->get('attribute_codes');
        $searchAttributesQuery->localeCode = $request->get('locale_code');

        $matchingAttributeCodes = $this->searchAttributesHandler->handle($searchAttributesQuery);

        return new JsonResponse($matchingAttributeCodes);
    }
}
