<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Controller;

use Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation\GetFileTemplateInformationHandler;
use Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation\GetFileTemplateInformationQuery;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\IsValidFileTemplateInformationQuery;
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
final class GetFileTemplateInformationAction
{
    public function __construct(
        private GetFileTemplateInformationHandler $getFileTemplateHandler,
        private ValidatorInterface $validator,
        private NormalizerInterface $violationNormalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $query = new GetFileTemplateInformationQuery();
        $query->fileKey = $request->get('file_key');
        $query->sheetName = $request->get('sheet_name');

        $violations = $this->validator->validate($query, new IsValidFileTemplateInformationQuery());
        if ($violations->count() > 0) {
            return new JsonResponse($this->violationNormalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $fileTemplateInformation = $this->getFileTemplateHandler->handle($query);

        return new JsonResponse($fileTemplateInformation->normalize());
    }
}
