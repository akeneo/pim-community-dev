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

use Akeneo\ReferenceEntity\Domain\Repository\MediaFileNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\MediaFileRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DownloadMediaFileAction
{
    private const FILE_STORAGE_ALIAS = 'catalogStorage';

    private MediaFileRepositoryInterface $mediaFileRepository;
    private FilesystemProvider $filesystemProvider;
    private SecurityFacade $securityFacade;
    private TokenStorageInterface $tokenStorage;
    private LoggerInterface $apiAclLogger;

    public function __construct(
        MediaFileRepositoryInterface $mediaFileRepository,
        FilesystemProvider $filesystemProvider,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $apiAclLogger
    ) {
        $this->mediaFileRepository = $mediaFileRepository;
        $this->filesystemProvider = $filesystemProvider;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->apiAclLogger = $apiAclLogger;
    }

    public function __invoke(string $fileCode): Response
    {
        $this->denyAccessUnlessAclIsGranted();

        $fileCode = urldecode($fileCode);

        try {
            $fileInfo = $this->mediaFileRepository->getByIdentifier($fileCode);
        } catch (MediaFileNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }

        $filesystem = $this->filesystemProvider->getFilesystem(self::FILE_STORAGE_ALIAS);
        if (!$filesystem->fileExists($fileCode)) {
            throw new NotFoundHttpException(sprintf('Media file "%s" is not present on the filesystem.', $fileCode));
        }

        $fileStream = $filesystem->readStream($fileCode);
        if (false === $fileStream) {
            throw new UnprocessableEntityHttpException(
                sprintf('Unable to fetch the file "%s" from the filesystem.', $fileCode)
            );
        }

        $headers = [
            'Content-Type'        => $fileInfo->getMimeType(),
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileInfo->getOriginalFilename())
        ];

        return new StreamedFileResponse($fileStream, 200, $headers);
    }

    private function denyAccessUnlessAclIsGranted(): void
    {
        $acl = 'pim_api_reference_entity_record_list';

        if (!$this->securityFacade->isGranted($acl)) {
            $token = $this->tokenStorage->getToken();
            if (null === $token) {
                throw new \LogicException('An user must be authenticated if ACLs are required');
            }

            $user = $token->getUser();
            if (!$user instanceof UserInterface) {
                throw new \LogicException(sprintf(
                    'An instance of "%s" is expected if ACLs are required',
                    UserInterface::class
                ));
            }

            $this->apiAclLogger->warning(sprintf(
                'User "%s" with roles %s is not granted "%s"',
                $user->getUsername(),
                implode(',', $user->getRoles()),
                $acl
            ));
        }
    }
}
