<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller\SampleData;

use Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData\GetRefreshedSampleDataHandler;
use Akeneo\Platform\TailoredImport\Application\SampleData\GetRefreshedSampleData\GetRefreshedSampleDataQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetRefreshedSampleDataAction
{
    public function __construct(
        private GetRefreshedSampleDataHandler $getRefreshedSampleDataHandler
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (
            null === $request->get('current_sample') ||
            null === $request->get('file_key') ||
            null === $request->get('column_index') ||
            null === $request->get('sheet_name') ||
            null === $request->get('product_line')
        ) {
            throw new \HttpInvalidParamException(
                'missing or null params, required params are "current_sample", "file_key", "column_index", "sheet_name", "product_line"'
            );
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
