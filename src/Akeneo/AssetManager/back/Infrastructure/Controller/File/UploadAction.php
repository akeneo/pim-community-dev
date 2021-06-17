<?php

namespace Akeneo\AssetManager\Infrastructure\Controller\File;

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * File upload action
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UploadAction
{
    protected ValidatorInterface $validator;

    protected PathGeneratorInterface $pathGenerator;

    private FileStorer $fileStorer;

    private FileInfoRepositoryInterface $fileInfoRepository;

    private NormalizerInterface $normalizer;

    public function __construct(
        ValidatorInterface $validator,
        PathGeneratorInterface $pathGenerator,
        FileStorer $fileStorer,
        FileInfoRepositoryInterface $fileInfoRepository,
        NormalizerInterface $normalizer
    ) {
        $this->validator = $validator;
        $this->pathGenerator = $pathGenerator;
        $this->fileStorer = $fileStorer;
        $this->fileInfoRepository = $fileInfoRepository;
        $this->normalizer = $normalizer;
    }

    /**
     * Post a new media and return it's temporary identifier
     *
     * @param Request $request
     *
     * @return Response
     */
    public function __invoke(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
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

        $file = $this->storeFile($uploadedFile);

        return new JsonResponse(
            [
                'originalFilename' => $uploadedFile->getClientOriginalName(),
                'filePath'         => $file->getKey()
            ]
        );
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
