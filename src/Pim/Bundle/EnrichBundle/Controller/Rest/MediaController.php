<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /** @var string */
    protected $uploadDir;

    /**
     * @param ValidatorInterface $validator
     * @param string             $uploadDir
     */
    public function __construct(ValidatorInterface $validator, $uploadDir)
    {
        $this->validator = $validator;
        $this->uploadDir = $uploadDir;
    }

    /**
     * Post a new media and return it's temporary identifier
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $file = $request->files->get('file');
        $violations = $this->validator->validate($file);

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

        try {
            $movedFile = $file->move(
                $this->uploadDir,
                uniqid() . '_' . $file->getClientOriginalName()
            );
        } catch (FileException $e) {
            return new JsonResponse(null, 400);
        }

        return new JsonResponse(
            [
                'originalFilename' => $file->getClientOriginalName(),
                'filePath'         => $movedFile->getPathname()
            ]
        );
    }
}
