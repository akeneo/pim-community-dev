<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Api\MediaFile;

use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UploadMediaFileAction
{
    public const FILE_STORAGE_ALIAS = 'catalogStorage';

    public function __construct(
        private FileStorerInterface $fileStorer,
        private RouterInterface $router,
        private SecurityFacade $securityFacade
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessAclIsGranted();

        $file = $request->files->has('file') ? $request->files->get('file') : null;

        if (null === $file) {
            throw new UnprocessableEntityHttpException('Property "file" is required.');
        }

        if (preg_match(
            '/[' . preg_quote('& \ + * ? [ ^ ] $ ( ) { } = ! < > | : - # @ ;', '/') . ']/',
            $file->getClientOriginalExtension()
        )) {
            throw new UnprocessableEntityHttpException('File extension cannot contain special characters.');
        }

        try {
            $fileInfo = $this->fileStorer->store($file, self::FILE_STORAGE_ALIAS, true);
        } catch (InvalidFile $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage(), $exception);
        }

        $downloadMediaFileUrl = $this->router->generate(
            'akeneo_reference_entities_media_file_rest_connector_download',
            ['fileCode' => $fileInfo->getKey()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $headers = [
            'Reference-entities-media-file-code' => $fileInfo->getKey(),
            'Location' => $downloadMediaFileUrl
        ];

        return new Response('', Response::HTTP_CREATED, $headers);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        if (!$this->securityFacade->isGranted('pim_api_reference_entity_record_edit')) {
            throw new AccessDeniedHttpException('Access forbidden. You are not allowed to create or update reference entity records.');
        }
    }
}
