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

use Akeneo\Platform\TailoredImport\Application\UploadStructureFile\UploadStructureFileCommand;
use Akeneo\Platform\TailoredImport\Application\UploadStructureFile\UploadStructureFileHandler;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\UploadedFile as UploadedFileConstraint;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadStructureFileAction
{
    public function __construct(
        private UploadStructureFileHandler $uploadFlatFileHandler,
        private ValidatorInterface $validator,
        private NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        /** @var ?UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
            return new JsonResponse([], 400);
        }

        $violations = $this->validator->validate($uploadedFile, new UploadedFileConstraint());

        if (count($violations) > 0) {
            return new JsonResponse($this->normalizer->normalize($violations), Response::HTTP_BAD_REQUEST);
        }

        $uploadFlatFileCommand = new UploadStructureFileCommand(
            $uploadedFile->getPathname(),
            $uploadedFile->getClientOriginalName()
        );

        $fileInfo = $this->uploadFlatFileHandler->handle($uploadFlatFileCommand);

        return new JsonResponse($fileInfo->normalize());
    }
}
