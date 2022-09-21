<?php

namespace Akeneo\Category\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Handler\StoreUploadedFile;
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
        private ValidatorInterface $validator,
        private NormalizerInterface $normalizer,
        private StoreUploadedFile $storeUploadedFile,
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
            new Assert\File(),
        ]);

        if (count($violations) > 0) {
            return new JsonResponse($this->normalizer->normalize($violations), 400);
        }

        $file = ($this->storeUploadedFile)($uploadedFile);

        return new JsonResponse([
            'originalFilename' => $uploadedFile->getClientOriginalName(),
            'filePath' => $file->getKey(),
            'size' => $file->getSize(),
            'mimeType' => $file->getMimeType(),
            'extension' => $file->getExtension(),
        ]);
    }
}
