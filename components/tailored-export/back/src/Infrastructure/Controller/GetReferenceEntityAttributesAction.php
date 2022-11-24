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

use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\AttributeDetails;
use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich\FindReferenceEntityAttributesInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GetReferenceEntityAttributesAction
{
    public function __construct(
        private FindReferenceEntityAttributesInterface $findReferenceEntityAttributes,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $referenceEntityCode = $request->get('reference_entity_code');
        if (null === $referenceEntityCode) {
            throw new BadRequestHttpException('Missing reference entity code');
        }

        $referenceEntityAttributes = $this->findReferenceEntityAttributes->findByCode($referenceEntityCode);

        return new JsonResponse(array_map(
            static fn (AttributeDetails $attribute) => $attribute->normalize(),
            $referenceEntityAttributes,
        ));
    }
}
