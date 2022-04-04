<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller\SampleData;

use Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData\GetRefreshedSampleDataHandler;
use Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData\GetRefreshedSampleDataQuery;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\RefreshSampleDataQuery;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\SampleDataQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
        $query->currentSample = $request->get('current_sample');
        $query->fileKey = $request->get('file_key');
        $query->columnIndex = intval($request->get('column_index'));
        $query->sheetName = $request->get('sheet_name');
        $query->productLine = intval($request->get('product_line'));

        $getRefreshedSampleDataResult = $this->getRefreshedSampleDataHandler->handle($query);

        return new JsonResponse($getRefreshedSampleDataResult->normalize(), Response::HTTP_OK);
    }
}
