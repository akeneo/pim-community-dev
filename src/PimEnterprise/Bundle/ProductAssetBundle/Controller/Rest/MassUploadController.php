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
use finfo;
use PimEnterprise\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Upload\Scheduler;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploaderInterface;
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
    protected $currentUser;

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
        $this->currentUser           = $tokenStorage->getToken()->getUser();
        $this->jobInstanceRepository = $jobInstanceRepository;

        $this->uploader      = $uploader;
        $this->uploadChecker = $uploadChecker;
        $this->scheduler     = $scheduler;

        $this->uploader->setSubDirectory($this->currentUser->getUsername());

        $this->scheduler->setSourceDirectory($this->uploader->getUserUploadDir());
        $this->scheduler->setScheduleDirectory($this->uploader->getUserScheduleDir());
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

        $valid = $this->uploadChecker->isValidFilename($filename);

        if (!$valid) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setData([
                'error' => $this->uploadChecker->getCheckStatus()
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

        $files = $request->files;

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
        $response = new JsonResponse();

        $filepath = $this->uploader
                ->getUserUploadDir() . DIRECTORY_SEPARATOR . $filename;

        if (is_file($filepath)) {
            unlink($filepath);
        }

        return $response;
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

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            foreach ($storedFiles as $file) {
                $filepath = $this->uploader->getUserUploadDir() . DIRECTORY_SEPARATOR . $file;
                $infos    = [
                    'name' => $file,
                    'type' => $finfo->file($filepath),
                    'size' => filesize($filepath),
                ];
                $files[]  = $infos;
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
        $response    = new JsonResponse();
        $result      = $this->scheduler->schedule();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('apply_assets_mass_upload');

        $this->jobLauncher->launch($jobInstance, $this->currentUser, '{}');

        $response->setData(['result' => $result]);

        return $response;
    }
}
