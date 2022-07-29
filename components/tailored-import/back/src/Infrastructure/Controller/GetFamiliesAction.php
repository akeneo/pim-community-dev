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

use Akeneo\Platform\TailoredImport\Infrastructure\Query\Family\FindFamilies;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\FindFamilies as FindFamiliesValidation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GetFamiliesAction
{
    private const LIMIT_DEFAULT = 25;

    public function __construct(
        private FindFamilies $findFamilies,
        private ValidatorInterface $validator,
        private NormalizerInterface $violationNormalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $violations = $this->validator->validate($request, new FindFamiliesValidation());
        if (0 < $violations->count()) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $findFamiliesResult = $this->findFamilies->execute(
            $request->get('locale'),
            $request->get('limit', self::LIMIT_DEFAULT),
            $request->get('page'),
            $request->get('search'),
            $request->get('include_codes'),
            $request->get('exclude_codes'),
        );

        return new JsonResponse($findFamiliesResult->normalize());
    }
}
