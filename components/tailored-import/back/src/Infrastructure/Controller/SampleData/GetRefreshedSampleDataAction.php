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

use Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData\GetRefreshedSampleDataHandler;
use Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData\GetRefreshedSampleDataQuery;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\SampleData\RefreshSampleDataQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GetRefreshedSampleDataAction
{
    public function __construct(
        private GetRefreshedSampleDataHandler $getRefreshedSampleDataHandler,
        private ValidatorInterface $validator,
        private NormalizerInterface $violationNormalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $violations = $this->validator->validate($request, new RefreshSampleDataQuery());
        if ($violations->count() > 0) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $query = new GetRefreshedSampleDataQuery();
        $query->currentSample = $request->get('current_sample', []);
        $query->fileKey = $request->get('file_key');
        $query->columnIndices = array_map('intval', $request->get('column_indices'));
        $query->sheetName = $request->get('sheet_name');
        $query->productLine = intval($request->get('product_line'));

        $getRefreshedSampleDataResult = $this->getRefreshedSampleDataHandler->handle($query);

        return new JsonResponse($getRefreshedSampleDataResult->normalize(), Response::HTTP_OK);
    }
}
