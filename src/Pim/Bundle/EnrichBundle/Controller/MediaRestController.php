<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Media controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaRestController
{
    /** @var string */
    protected $uploadDir;

    /**
     * @param string $uploadDir
     */
    public function __construct($uploadDir)
    {
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

        $movedFile = $file->move(
            $this->uploadDir . '/',
            uniqid() . '_' . $file->getClientOriginalName()
        );

        return new JsonResponse(
            [
                'originalFilename' => $file->getClientOriginalName(),
                'filePath'         => $movedFile->getPathname()
            ]
        );
    }
}
