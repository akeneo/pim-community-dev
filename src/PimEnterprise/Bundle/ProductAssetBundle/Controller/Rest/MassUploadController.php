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
use PimEnterprise\Component\ProductAsset\Upload\Scheduler;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploaderInterface;
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

    /** @var UploaderInterface */
    protected $uploader;

    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var Scheduler */
    protected $scheduler;

    /** @var UserInterface */
    protected $tokenStorage;

    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /**
     * @param AssetRepositoryInterface $assetRepository
     * @param UploaderInterface        $uploader
     * @param UploadCheckerInterface   $uploadChecker
     * @param Scheduler                $scheduler
     * @param TokenStorageInterface    $tokenStorage
     * @param JobLauncherInterface     $jobLauncher
     * @param JobInstanceRepository    $jobInstanceRepository
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        UploaderInterface $uploader,
        UploadCheckerInterface $uploadChecker,
        Scheduler $scheduler,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository
    ) {
        $this->assetRepository       = $assetRepository;
        $this->jobLauncher           = $jobLauncher;
        $this->tokenStorage          = $tokenStorage;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->uploader              = $uploader;
        $this->uploadChecker         = $uploadChecker;
        $this->scheduler             = $scheduler;
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

        $uploadStatus = $this->uploadChecker
            ->checkFilename($filename, $this->uploader->getUserUploadDir(), $this->uploader->getUserScheduleDir());

        if ($this->uploadChecker->isError($uploadStatus)) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setData([
                'error' => $uploadStatus
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
            $file     = $files->getIterator()->current();
            $uploaded = $this->uploader->upload($file);
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
        $filepath = $this->uploader
                ->getUserUploadDir() . DIRECTORY_SEPARATOR . $filename;

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
        $response = new JsonResponse();

        $files = [];

        if (is_dir($this->uploader->getUserUploadDir())) {
            $storedFiles = array_diff(scandir($this->uploader->getUserUploadDir()), ['.', '..']);

            $mimeTypeGuesser = MimeTypeGuesser::getInstance();

            foreach ($storedFiles as $file) {
                $filepath = $this->uploader->getUserUploadDir() . DIRECTORY_SEPARATOR . $file;
                $mimeType = $mimeTypeGuesser->guess($filepath);
                $files[]  = [
                    'name' => $file,
                    'type' => $mimeType,
                    'size' => filesize($filepath),
                ];
            }
        }

        $response->setData(['files' => $files]);

        return $response;
    }

    /**
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function scheduleAction()
    {
        $this->scheduler->setSourceDirectory($this->uploader->getUserUploadDir());
        $this->scheduler->setScheduleDirectory($this->uploader->getUserScheduleDir());

        $result      = $this->scheduler->schedule();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('apply_assets_mass_upload');

        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), '{}');

        $response = new JsonResponse(['result' => $result]);

        return $response;
    }
}
