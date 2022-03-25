<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Application\GetSampleData\GetSampleDataHandler;
use Akeneo\Platform\TailoredImport\Application\GetSampleData\GetSampleDataQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetSampleDataAction
{
    public function __construct(
        private GetSampleDataHandler $getSampleDataHandler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (null === $request->get('job_code') || null === $request->get('column_index')) {
            throw new \HttpInvalidParamException('missing or null params, required params are "job_code" and "column_index"');
        }

        $query = new GetSampleDataQuery();
        $query->jobCode = $request->get('job_code');
        $query->columnIndex = $request->get('column_index');

        $sampleData = $this->getSampleDataHandler->handle($query);

        return new JsonResponse($sampleData->normalize(), Response::HTTP_OK);
    }
}
