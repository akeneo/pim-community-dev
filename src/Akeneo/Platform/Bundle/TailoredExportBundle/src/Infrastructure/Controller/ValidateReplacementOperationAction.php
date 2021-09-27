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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Controller;

use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation\ReplacementOperationConstraint;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidateReplacementOperationAction
{
    private ValidatorInterface $validator;
    private NormalizerInterface $violationNormalizer;

    public function __construct(
        ValidatorInterface $validator,
        NormalizerInterface $violationNormalizer
    ) {
        $this->validator = $validator;
        $this->violationNormalizer = $violationNormalizer;
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $decodedRequest = $this->decodeRequest($request);

        $violations = $this->validator->validate($decodedRequest, new ReplacementOperationConstraint());
        if ($violations->count() > 0) {
            return new JsonResponse($this->violationNormalizer->normalize(
                $violations,
                'internal_api',
                [
                    'translate' => false
                ]
            ), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new Response(null, Response::HTTP_OK);
    }

    private function decodeRequest(Request $request): array
    {
        $decodedRequest = json_decode($request->getContent(), true);

        if (null === $decodedRequest) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedRequest;
    }
}
