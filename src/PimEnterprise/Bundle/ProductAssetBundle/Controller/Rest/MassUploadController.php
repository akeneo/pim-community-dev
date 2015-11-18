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
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PimEnterprise\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Upload\Exception\UploadException;
use PimEnterprise\Component\ProductAsset\Upload\SchedulerInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadCheckerInterface;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
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

    /** @var UploadCheckerInterface */
    protected $uploadChecker;

    /** @var SchedulerInterface */
    protected $scheduler;

    /** @var UserInterface */
    protected $tokenStorage;

    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepo;

    /** @var string */
    protected $tmpStorageDir;

    /**
     * @param AssetRepositoryInterface $assetRepository
     * @param UploadCheckerInterface   $uploadChecker
     * @param SchedulerInterface       $scheduler
     * @param TokenStorageInterface    $tokenStorage
     * @param JobLauncherInterface     $jobLauncher
     * @param JobInstanceRepository    $jobInstanceRepository
     * @param string                   $tmpStorageDir
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        UploadCheckerInterface $uploadChecker,
        SchedulerInterface $scheduler,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        $tmpStorageDir
    ) {
        $this->assetRepository = $assetRepository;
        $this->uploadChecker   = $uploadChecker;
        $this->scheduler       = $scheduler;
        $this->tokenStorage    = $tokenStorage;
        $this->jobLauncher     = $jobLauncher;
        $this->jobInstanceRepo = $jobInstanceRepository;
        $this->tmpStorageDir   = $tmpStorageDir;
    }

    /**
     * @AclAncestor("pimee_product_asset_mass_upload")
     *
     * @param string $filename
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function verifyAction($filename)
    {
        $response      = new JsonResponse();
        $uploadContext = $this->getUploadContext();

        try {
            $parsedFilename = $this->uploadChecker->getParsedFilename($this->cleanFilename($filename));
            $this->uploadChecker
                ->validateUpload(
                    $parsedFilename,
                    $uploadContext->getTemporaryUploadDirectory(),
                    $uploadContext->getTemporaryScheduleDirectory()
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
     * @AclAncestor("pimee_product_asset_mass_upload")
     *
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
            $file             = $files->getIterator()->current();
            $originalFilename = $file->getClientOriginalName();
            $parsedFilename   = $this->uploadChecker->getParsedFilename($originalFilename);
            $targetDir        = $this->getUploadContext()->getTemporaryUploadDirectory();
            $uploaded         = $file->move($targetDir, $parsedFilename->getRawFilename());
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
     * @AclAncestor("pimee_product_asset_mass_upload")
     *
     * @param $filename
     *
     * @return JsonResponse
     */
    public function deleteUploadedFileAction($filename)
    {
        $filepath = $this->getUploadContext()->getTemporaryUploadDirectory()
            . DIRECTORY_SEPARATOR . $this->cleanFilename($filename);

        $response = new JsonResponse();

        try {
            $fs = new Filesystem();
            $fs->remove($filepath);
        } catch (IOException $e) {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        return $response;
    }

    /**
     * @AclAncestor("pimee_product_asset_mass_upload")
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $uploadContext      = $this->getUploadContext();
        $tmpUploadDirectory = $uploadContext->getTemporaryUploadDirectory();
        $files              = [];

        if (is_dir($tmpUploadDirectory)) {
            $storedFiles = array_diff(scandir($tmpUploadDirectory), ['.', '..']);

            $mimeTypeGuesser = MimeTypeGuesser::getInstance();

            foreach ($storedFiles as $file) {
                $filepath = $uploadContext->getTemporaryUploadDirectory() . DIRECTORY_SEPARATOR . $file;
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
     * @AclAncestor("pimee_product_asset_mass_upload")
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function scheduleAction()
    {
        $result      = $this->scheduler->schedule($this->getUploadContext());
        $jobInstance = $this->jobInstanceRepo->findOneByIdentifier('apply_assets_mass_upload');

        $jobExecution = $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), '{}');

        return new JsonResponse([
            'result' => $result,
            'jobId'  => $jobExecution->getId(),
        ]);
    }

    /**
     * @return UploadContext
     */
    protected function getUploadContext()
    {
        if (null === $this->tokenStorage->getToken()) {
            throw new \RuntimeException('Mass upload needs an authenticated user');
        }

        $username = $this->tokenStorage->getToken()->getUsername();

        return new UploadContext($this->tmpStorageDir, $username);
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
        return basename(trim($filename));
    }
}
