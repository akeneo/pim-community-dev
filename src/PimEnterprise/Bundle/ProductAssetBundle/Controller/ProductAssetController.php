<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Controller;

use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Bundle\ProductAssetBundle\Updater\FilesUpdaterInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\FileMetadataRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ReferenceRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;
use PimEnterprise\Component\ProductAsset\VariationFileGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Asset controller
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ProductAssetController extends Controller
{
    /** @staticvar string */
    const BACK_TO_GRID = 'BackGrid';

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var FileMetadataRepositoryInterface */
    protected $metadataRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var ReferenceRepositoryInterface */
    protected $referenceRepository;

    /** @var VariationRepositoryInterface */
    protected $variationRepository;

    /** @var VariationFileGeneratorInterface */
    protected $variationFileGenerator;

    /** @var FilesUpdaterInterface */
    protected $assetFilesUpdater;

    /** @var SaverInterface */
    protected $assetSaver;

    /** @var SaverInterface */
    protected $referenceSaver;

    /** @var SaverInterface */
    protected $variationSaver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param AssetRepositoryInterface        $assetRepository
     * @param ReferenceRepositoryInterface    $referenceRepository
     * @param VariationRepositoryInterface    $variationRepository
     * @param FileMetadataRepositoryInterface $metadataRepository
     * @param ChannelRepositoryInterface      $channelRepository
     * @param VariationFileGeneratorInterface $variationFileGenerator
     * @param FilesUpdaterInterface           $assetFilesUpdater
     * @param SaverInterface                  $assetSaver
     * @param SaverInterface                  $referenceSaver
     * @param SaverInterface                  $variationSaver
     * @param EventDispatcherInterface        $eventDispatcher
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        ReferenceRepositoryInterface $referenceRepository,
        VariationRepositoryInterface $variationRepository,
        FileMetadataRepositoryInterface $metadataRepository,
        ChannelRepositoryInterface $channelRepository,
        VariationFileGeneratorInterface $variationFileGenerator,
        FilesUpdaterInterface $assetFilesUpdater,
        SaverInterface $assetSaver,
        SaverInterface $referenceSaver,
        SaverInterface $variationSaver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->assetRepository        = $assetRepository;
        $this->referenceRepository    = $referenceRepository;
        $this->variationRepository    = $variationRepository;
        $this->metadataRepository     = $metadataRepository;
        $this->channelRepository      = $channelRepository;
        $this->variationFileGenerator = $variationFileGenerator;
        $this->assetFilesUpdater      = $assetFilesUpdater;
        $this->assetSaver             = $assetSaver;
        $this->referenceSaver         = $referenceSaver;
        $this->variationSaver         = $variationSaver;
        $this->eventDispatcher        = $eventDispatcher;
    }

    /**
     * List of assets
     *
     * @Template
     * @AclAncestor("pimee_product_asset_index")
     *
     * @return array
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * View an asset
     *
     * @param int|string $id
     *
     * @Template
     * @AclAncestor("pimee_product_asset_index")
     *
     * @return array
     */
    public function viewAction($id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        $references   = $productAsset->getReferences();

        $attachments = [];
        foreach ($references as $refKey => $reference) {
            $attachments[$refKey]['reference'] = $reference;

            foreach ($reference->getVariations() as $variation) {
                $metadata = null;
                if (null !== $variation->getFile()) {
                    $metadata = $this->metadataRepository->findOneBy(
                        [
                            'file' => $variation->getFile()->getId()
                        ]
                    );
                }

                $attachments[$refKey]['variations'][] = [
                    'entity'   => $variation,
                    'metadata' => $metadata
                ];
            }
        }

        return [
            'asset'       => $productAsset,
            'attachments' => $attachments
        ];
    }

    /**
     * Edit an asset
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @AclAncestor("pimee_product_asset_index")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        $assetLocales = $productAsset->getLocales();

        if (null !== $request->get('locale')) {
            $locale = $assetLocales[$request->get('locale')];
        } elseif (!empty($assetLocales)) {
            $locale = reset($assetLocales);
        } else {
            $locale = null;
        }

        $assetForm    = $this->createForm('pimee_product_asset', $productAsset);

        $assetForm->handleRequest($request);

        if ($assetForm->isValid()) {
            try {
                $this->assetFilesUpdater->updateAssetFiles($productAsset);
                $this->assetSaver->save($productAsset);
                $this->eventDispatcher->dispatch(
                    AssetEvent::FILES_UPLOAD_POST,
                    new AssetEvent($productAsset)
                );
                $this->addFlash($request, 'success', 'pimee_product_asset.enrich_asset.flash.update.success');
            } catch (\Exception $e) {
                $this->addFlash($request, 'error', 'pimee_product_asset.enrich_asset.flash.update.error');
            }
            return $this->redirectAfterEdit($request, ['id' => $id]);
        } elseif ($assetForm->isSubmitted()) {
            $this->assetFilesUpdater->resetAllUploadedFiles($productAsset);
        }


        $metadata = null;
        if (null !== $productAsset) {
            $metadata = $this->getAssetMetadata($productAsset);
        }

        return $this->render('PimEnterpriseProductAssetBundle:ProductAsset:edit.html.twig', [
            'asset'         => $productAsset,
            'form'          => $assetForm->createView(),
            'metadata'      => $metadata,
            'currentLocale' => $locale,
        ]);
    }

    /**
     * Delete a variation and redirect
     *
     * @param Request    $request
     * @param int|string reference $id
     *
     * @return RedirectResponse
     */
    public function deleteReferenceFileAction(Request $request, $id)
    {
        $reference = $this->findAssetReferenceOr404($id);
        $asset = $reference->getAsset();

        try {
            $this->assetFilesUpdater->deleteReferenceFile($reference);
            $this->referenceSaver->save($reference);
            $this->addFlash($request, 'success', 'pimee_product_asset.enrich_reference.flash.delete.success');
        } catch (\Exception $e) {
            $this->addFlash($request, 'error', 'pimee_product_asset.enrich_reference.flash.delete.error');
        }

        $parameters = ['id' => $asset->getId()];

        if (null !== $reference->getLocale()) {
            $parameters['locale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * Delete a variation and redirect
     *
     * @param Request    $request
     * @param int|string variation $id
     *
     * @return RedirectResponse
     */
    public function deleteVariationFileAction(Request $request, $id)
    {
        $variation = $this->findAssetVariationOr404($id);
        $asset = $variation->getAsset();
        $reference = $variation->getReference();

        try {
            $this->assetFilesUpdater->deleteVariationFile($variation);
            $this->variationSaver->save($variation);
            $this->addFlash($request, 'success', 'pimee_product_asset.enrich_variation.flash.delete.success');
        } catch (\Exception $e) {
            $this->addFlash($request, 'error', 'pimee_product_asset.enrich_variation.flash.delete.error');
        }

        $parameters = ['id' => $asset->getId()];

        if (null !== $reference->getLocale()) {
            $parameters['locale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * Reset a variation file with the reference and redirect
     *
     * @param Request    $request
     * @param int|string variation $id
     *
     * @return RedirectResponse
     */
    public function resetVariationFileAction(Request $request, $id)
    {
        $variation = $this->findAssetVariationOr404($id);
        $asset = $variation->getAsset();
        $reference = $variation->getReference();

        try {
            $this->assetFilesUpdater->resetVariationFile($variation);
            $this->variationSaver->save($variation);
            $this->eventDispatcher->dispatch(
                AssetEvent::FILES_UPLOAD_POST,
                new AssetEvent($asset)
            );
            $this->addFlash($request, 'success', 'pimee_product_asset.enrich_asset.flash.update.success');
        } catch (\Exception $e) {
            $this->addFlash($request, 'error', 'pimee_product_asset.enrich_asset.flash.update.error');
        }

        $parameters = ['id' => $asset->getId()];

        if (null !== $reference->getLocale()) {
            $parameters['locale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * Reset all variation files with the reference file and redirect
     *
     * @param Request    $request
     * @param int|string variation $id
     *
     * @return RedirectResponse
     */
    public function resetVariationsFilesAction(Request $request, $id)
    {
        $reference = $this->findAssetReferenceOr404($id);
        $asset = $reference->getAsset();

        try {
            $this->assetFilesUpdater->resetAllVariationsFiles($reference, true);
            $this->assetSaver->save($asset);
            $this->eventDispatcher->dispatch(
                AssetEvent::FILES_UPLOAD_POST,
                new AssetEvent($asset)
            );
            $this->addFlash($request, 'success', 'pimee_product_asset.enrich_asset.flash.update.success');
        } catch (\Exception $e) {
            $this->addFlash($request, 'error', 'pimee_product_asset.enrich_asset.flash.update.error');
        }

        $parameters = ['id' => $asset->getId()];

        if (null !== $reference->getLocale()) {
            $parameters['locale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * @param AssetInterface $productAsset
     *
     * @return array
     */
    protected function getAssetMetadata(AssetInterface $productAsset)
    {
        $metadata = [];

        foreach ($productAsset->getReferences() as $reference) {
            $referenceFileMeta = $reference->getFile() ? $this->getFileMetadata($reference->getFile()) : null;
            $metadata['references'][$reference->getId()] = $referenceFileMeta;

            foreach ($reference->getVariations() as $variation) {
                $variationFileMeta = $variation->getFile() ? $this->getFileMetadata($variation->getFile()) : null;
                $metadata['variations'][$variation->getId()] = $variationFileMeta;
            }
        }

        return $metadata;
    }

    /**
     * @param FileInterface $file
     *
     * @return FileMetadataInterface
     */
    protected function getFileMetadata(FileInterface $file)
    {
        $metadata = $this->metadataRepository->findOneBy(['file' => $file->getId()]);

        return $metadata;
    }

    /**
     * Add flash message
     *
     * @param Request $request    the request
     * @param string  $type       the flash type
     * @param string  $message    the flash message
     * @param array   $parameters the flash message parameters
     */
    protected function addFlash(Request $request, $type, $message, array $parameters = [])
    {
        $request->getSession()->getFlashBag()->add($type, new Message($message, $parameters));
    }

    /**
     * Switch case to redirect after saving a product asset from the edit form
     *
     * @param Request $request
     * @param array   $params Request parameters
     *
     * @return Response
     */
    protected function redirectAfterEdit(Request $request, array $params)
    {
        switch ($request->get('action')) {
            case self::BACK_TO_GRID:
                $route  = 'pimee_product_asset_index';
                $params = [];
                break;
            default:
                if (null !== $request->get('locale')) {
                    $params['locale'] = $request->get('locale');
                }
                $route = 'pimee_product_asset_edit';
                break;
        }

        return $this->redirect($this->generateUrl($route, $params));
    }

    /**
     * Find an Asset by its id or return a 404 response
     *
     * @param int|string $id
     *
     * @throws NotFoundHttpException
     *
     * @return AssetInterface
     */
    protected function findProductAssetOr404($id)
    {
        $productAsset = $this->assetRepository->find($id);

        if (null === $productAsset) {
            throw $this->createNotFoundException(
                sprintf('Product asset with id "%s" could not be found.', (string) $id)
            );
        }

        return $productAsset;
    }

    /**
     * Find a reference by its id or return a 404 response
     *
     * @param int|string $id
     *
     * @throws NotFoundHttpException
     *
     * @return ReferenceInterface
     */
    protected function findAssetReferenceOr404($id)
    {
        $reference = $this->referenceRepository->find($id);

        if (null === $reference) {
            throw $this->createNotFoundException(
                sprintf('Asset reference with id "%s" could not be found.', (string) $id)
            );
        }

        return $reference;
    }

    /**
     * Find a variation by its id or return a 404 response
     *
     * @param int|string $id
     *
     * @throws NotFoundHttpException
     *
     * @return VariationInterface
     */
    protected function findAssetVariationOr404($id)
    {
        $variation = $this->variationRepository->find($id);

        if (null === $variation) {
            throw $this->createNotFoundException(
                sprintf('Asset variation with id "%s" could not be found.', (string) $id)
            );
        }

        return $variation;
    }
}
