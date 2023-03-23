<?php

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Domain\Filesystem\Storage;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UploadController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly NormalizerInterface $normalizer,
        private readonly FileStorer $fileStorer,
        private readonly ?array $supportedMimeTypes = [],
    ) {
    }

    public function __invoke(Request $request): RedirectResponse|JsonResponse|Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
            return new JsonResponse([], 400);
        }

        $violations = $this->validator->validate($uploadedFile, [
            new Assert\Valid(),
            new Assert\File(mimeTypes: $this->supportedMimeTypes),
        ]);

        if (count($violations) > 0) {
            return new JsonResponse($this->normalizer->normalize($violations), 400);
        }

        $file = $this->fileStorer->store($uploadedFile, Storage::CATEGORY_STORAGE_ALIAS);

        return new JsonResponse([
            'originalFilename' => $uploadedFile->getClientOriginalName(),
            'filePath' => $file->getKey(),
            'size' => $file->getSize(),
            'mimeType' => $file->getMimeType(),
            'extension' => $file->getExtension(),
        ]);
    }
}
