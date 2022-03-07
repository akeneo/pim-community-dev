<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation\GetFileTemplateInformationHandler;
use Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation\GetFileTemplateInformationQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetFileTemplateInformationAction
{
    public function __construct(
        private GetFileTemplateInformationHandler $getFileTemplateHandler,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        if (null === $request->get('file_key')) {
            return new JsonResponse(status: 400);
        }

        $query = new GetFileTemplateInformationQuery();
        $query->fileKey = $request->get('file_key');
        $query->sheetName = $request->get('sheet_name');
        $query->headerLine = $request->query->getInt('header_line', 1);

        $fileTemplateInformation = $this->getFileTemplateHandler->handle($query);

        return new JsonResponse($fileTemplateInformation->normalize(), 200);
    }
}
