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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Controller;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\GetReferenceEntityAttributesQuery;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\AttributeDetails;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindReferenceEntityAttributesInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GetReferenceEntityAttributesAction
{
    /**
     * @param array<string> $supportedAttributeTypes
     */
    public function __construct(
        private FindReferenceEntityAttributesInterface $findReferenceEntityAttributes,
        private ValidatorInterface $validator,
        private NormalizerInterface $violationNormalizer,
        private array $supportedAttributeTypes,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $violations = $this->validator->validate($request, new GetReferenceEntityAttributesQuery());
        if (0 < $violations->count()) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $referenceEntityAttributes = $this->findReferenceEntityAttributes->findByCode(
            $request->get('reference_entity_code'),
            $this->supportedAttributeTypes,
        );

        return new JsonResponse(array_map(
            static fn (AttributeDetails $attribute) => $attribute->normalize(),
            $referenceEntityAttributes,
        ));
    }
}
