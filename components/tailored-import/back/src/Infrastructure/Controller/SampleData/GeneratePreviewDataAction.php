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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller\SampleData;

use Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData\GeneratePreviewDataHandler;
use Akeneo\Platform\TailoredImport\Application\SampleData\GeneratePreviewData\GeneratePreviewDataQuery;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\SampleData\GeneratePreviewData;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GeneratePreviewDataAction
{
    public function __construct(
        private GeneratePreviewDataHandler $generatePreviewDataHandler,
        private ValidatorInterface $validator,
        private NormalizerInterface $violationNormalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $violations = $this->validator->validate($request, new GeneratePreviewData());
        if ($violations->count() > 0) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $query = new GeneratePreviewDataQuery();
        $query->sampleData = $request->get('sample_data');
        $query->operations = $request->get('operations');

        $generatePreviewDataResult = $this->generatePreviewDataHandler->handle($query);

        return new JsonResponse($generatePreviewDataResult->normalize(), Response::HTTP_OK);
    }
}
