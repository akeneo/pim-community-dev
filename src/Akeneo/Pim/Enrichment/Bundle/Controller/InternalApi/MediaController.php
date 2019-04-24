<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Path;
use Akeneo\Tool\Component\FileStorage\PathGeneratorInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
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

    /** @var string */
    protected $uploadDir;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    public function __construct(
        ValidatorInterface $validator,
        PathGeneratorInterface $pathGenerator,
        FilesystemProvider $filesystemProvider,
        $uploadDir
    )
    {
        $this->validator = $validator;
        $this->pathGenerator = $pathGenerator;
        $this->filesystemProvider = $filesystemProvider;
        $this->uploadDir = $uploadDir;
    }

    /**
     * Post a new media and return it's temporary identifier
     */
    public function postAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $violations = $this->validator->validate($file);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = [
                    'message' => $violation->getMessage(),
                    'invalid_value' => $violation->getInvalidValue(),
                ];
            }

            return new JsonResponse($errors, 400);
        }

        $pathData = $this->pathGenerator->generate($file);

        try {
            $fileSystem = $this->filesystemProvider->getFilesystem('pefTmpStorage');

            $stream = fopen($file->getPathname(), 'r+');
            $pathname = new Path($pathData['path'], $pathData['uuid'], $file->getClientOriginalName());
            $fileSystem->writeStream(
                (string)$pathname,
                $stream
            );

            if (is_resource($stream)) {
                fclose($stream);
            }
        } catch (FileExistsException | \InvalidArgumentException $e) {
            return new JsonResponse("Unable to create target-directory, or moving file.", 400);
        }

        return new JsonResponse([
            'originalFilename' => $file->getClientOriginalName(),
            'filePath' => (string)$pathname,
        ]);
    }
}
