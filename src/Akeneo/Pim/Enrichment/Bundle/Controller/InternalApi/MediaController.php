<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\PathGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Media controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaController
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var PathGeneratorInterface */
    protected $pathGenerator;

    /** @var FileStorer */
    private $fileStorer;

    public function __construct(ValidatorInterface $validator, PathGeneratorInterface $pathGenerator, FileStorer $fileStorer)
    {
        $this->validator = $validator;
        $this->pathGenerator = $pathGenerator;
        $this->fileStorer = $fileStorer;
    }

    /**
     * Post a new media and return it's temporary identifier
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $violations = $this->validator->validate($uploadedFile);

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

    protected function storeFile(UploadedFile $uploadedFile): FileInfoInterface
    {
        return $this->fileStorer->store($uploadedFile, FileStorage::CATALOG_STORAGE_ALIAS);
    }
}
