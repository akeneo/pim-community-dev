<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\PathGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UploadImageAction
{
    public function __construct(
        protected ValidatorInterface $validator,
        protected PathGeneratorInterface $pathGenerator,
        private FileStorer $fileStorer,
        private readonly array $supportedTypes = [],
    ) {
    }

    public function __invoke(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $violations = $this->validator->validate($uploadedFile, [
            new Constraints\UploadedFile([
                'types' => $this->supportedTypes
            ]),
        ]);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = [
                    'message'       => $violation->getMessage(),
                    'invalid_value' => $violation->getInvalidValue()
                ];
            }

            return new JsonResponse($errors, 400);
        }

        $file = $this->storeFile($uploadedFile);

        return new JsonResponse(
            [
                'originalFilename' => $uploadedFile->getClientOriginalName(),
                'filePath'         => $file->getKey()
            ]
        );
    }

    private function storeFile(UploadedFile $uploadedFile): FileInfoInterface
    {
        return $this->fileStorer->store($uploadedFile, FileStorage::CATALOG_STORAGE_ALIAS);
    }
}
