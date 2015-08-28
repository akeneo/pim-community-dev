<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Controller\Rest;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use PimEnterprise\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Upload\Exception\UploadException;
use PimEnterprise\Component\ProductAsset\Upload\SchedulerInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Asset mass upload controller
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class MassUploadController
{
    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var UploadContext */
    protected $uploadContext;

    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var SchedulerInterface */
    protected $scheduler;

    /** @var UserInterface */
    protected $tokenStorage;

    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /**
     * @param AssetRepositoryInterface $assetRepository
     * @param UploadContext            $uploadContext
     * @param UploadCheckerInterface   $uploadChecker
     * @param SchedulerInterface       $scheduler
     * @param TokenStorageInterface    $tokenStorage
     * @param JobLauncherInterface     $jobLauncher
     * @param JobInstanceRepository    $jobInstanceRepository
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        UploadContext $uploadContext,
        UploadCheckerInterface $uploadChecker,
        SchedulerInterface $scheduler,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository
    ) {
        $this->assetRepository       = $assetRepository;
        $this->uploadContext         = $uploadContext;
        $this->uploadChecker         = $uploadChecker;
        $this->scheduler             = $scheduler;
        $this->tokenStorage          = $tokenStorage;
        $this->jobLauncher           = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;

        $username = $this->tokenStorage->getToken()->getUsername();
        $this->uploadContext->setUsername($username);
    }

    /**
     * @param $filename
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function verifyAction($filename)
    {
        $response = new JsonResponse();

        try {
            $this->uploadChecker
                ->validateSchedule($this->cleanFilename($filename),
                    $this->uploadContext->getTemporaryUploadDirectory(),
                    $this->uploadContext->getTemporaryScheduleDirectory()
                );
        } catch (UploadException $e) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setData([
                'error' => $e->getMessage()
            ]);
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $response = new JsonResponse();
        $files    = $request->files;
        $uploaded = null;

        if ($files->count() > 0) {
            $file = $files->getIterator()->current();

            $filename  = $file->getClientOriginalName();
            $targetDir = $this->uploadContext->getTemporaryUploadDirectory();

            $uploaded = $file->move($targetDir, $filename);
        }

        if (null === $uploaded) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->setData([
                'error' => 'pimee_product_asset.mass_upload.error.upload'
            ]);
        }

        return $response;
    }

    /**
     * @param $filename
     *
     * @return JsonResponse
     */
    public function deleteUploadedFileAction($filename)
    {
        $filepath = $this->uploadContext->getTemporaryUploadDirectory()
            . DIRECTORY_SEPARATOR . $this->cleanFilename($filename);

        if (is_file($filepath)) {
            unlink($filepath);
        }

        return new JsonResponse();
    }

    /**
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $temporaryUploadDirectory = $this->uploadContext->getTemporaryUploadDirectory();
        $files                    = [];

        if (is_dir($temporaryUploadDirectory)) {
            $storedFiles = array_diff(scandir($temporaryUploadDirectory), ['.', '..']);

            $mimeTypeGuesser = MimeTypeGuesser::getInstance();

            foreach ($storedFiles as $file) {
                $filepath = $this->uploadContext->getTemporaryUploadDirectory() . DIRECTORY_SEPARATOR . $file;
                $mimeType = $mimeTypeGuesser->guess($filepath);
                $files[]  = [
                    'name' => $file,
                    'type' => $mimeType,
                    'size' => filesize($filepath),
                ];
            }
        }

        return new JsonResponse(['files' => $files]);
    }

    /**
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function scheduleAction()
    {
        $result      = $this->scheduler->schedule($this->uploadContext);
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('apply_assets_mass_upload');

        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), '{}');

        return new JsonResponse(['result' => $result]);
    }

    /**
     * Clean a requested filename
     *
     * @param $filename
     *
     * @return string
     */
    protected function cleanFilename($filename)
    {
        $clean = basename(trim($filename));

        return $clean;
    }
}
