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

use Akeneo\Platform\TailoredImport\Domain\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\UploadedFlatFile;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\PathGeneratorInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadAction
{
    public function __construct(
        protected ValidatorInterface $validator,
        protected PathGeneratorInterface $pathGenerator,
        private FileStorer $fileStorer,
        private FileInfoRepositoryInterface $fileInfoRepository,
        private NormalizerInterface $normalizer
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

        $violations = $this->validator->validate($uploadedFile, new UploadedFlatFile());

        if (count($violations) > 0) {
            return new JsonResponse($this->normalizer->normalize($violations), 400);
        }

        $file = $this->storeFile($uploadedFile);

        return new JsonResponse(['file_key' => $file->getKey()]);
    }

    protected function storeFile(UploadedFile $uploadedFile): FileInfoInterface
    {
        $hash = sha1_file($uploadedFile->getPathname());
        $originalFilename = $uploadedFile->getClientOriginalName();
        $file = $this->fileInfoRepository->findOneBy(
            [
                'hash'             => $hash,
                'originalFilename' => $originalFilename,
                'storage'          => Storage::FILE_STORAGE_ALIAS
            ]
        );

        if (null === $file) {
            $file = $this->fileStorer->store($uploadedFile, Storage::FILE_STORAGE_ALIAS);
        }

        return $file;
    }
}
