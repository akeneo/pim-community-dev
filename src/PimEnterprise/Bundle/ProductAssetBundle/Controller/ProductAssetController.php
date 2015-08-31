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

use Akeneo\Component\FileStorage\FileFactoryInterface;
use Akeneo\Component\FileStorage\Model\FileInterface;
use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use Akeneo\Component\FileTransformer\Exception\NonRegisteredTransformationException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\GenericTransformationException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\ImageHeightException;
use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformation\ImageWidthException;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\EnrichBundle\Controller\FileController;
use Pim\Bundle\EnrichBundle\Flash\Message;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Component\ProductAsset\Factory\AssetFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\FileMetadataInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\FileMetadataRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ReferenceRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
use PimEnterprise\Component\ProductAsset\VariationFileGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

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

    /** @var RemoverInterface */
    protected $assetRemover;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var AssetFactory */
    protected $assetFactory;

    /** @var FileFactoryInterface */
    protected $fileFactory;

    /** @var UserContext */
    protected $userContext;

    /** @var FileController */
    protected $fileController;

    /**
     * @param AssetRepositoryInterface        $assetRepository
     * @param ReferenceRepositoryInterface    $referenceRepository
     * @param VariationRepositoryInterface    $variationRepository
     * @param FileMetadataRepositoryInterface $metadataRepository
     * @param LocaleRepositoryInterface       $localeRepository
     * @param ChannelRepositoryInterface      $channelRepository
     * @param VariationFileGeneratorInterface $variationFileGenerator
     * @param FilesUpdaterInterface           $assetFilesUpdater
     * @param SaverInterface                  $assetSaver
     * @param SaverInterface                  $referenceSaver
     * @param SaverInterface                  $variationSaver
     * @param RemoverInterface                $assetRemover
     * @param EventDispatcherInterface        $eventDispatcher
     * @param AssetFactory                    $assetFactory
     * @param FileFactoryInterface            $fileFactory
     * @param UserContext                     $userContext
     * @param FileController                  $fileController
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        ReferenceRepositoryInterface $referenceRepository,
        VariationRepositoryInterface $variationRepository,
        FileMetadataRepositoryInterface $metadataRepository,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository,
        VariationFileGeneratorInterface $variationFileGenerator,
        FilesUpdaterInterface $assetFilesUpdater,
        SaverInterface $assetSaver,
        SaverInterface $referenceSaver,
        SaverInterface $variationSaver,
        RemoverInterface $assetRemover,
        EventDispatcherInterface $eventDispatcher,
        AssetFactory $assetFactory,
        FileFactoryInterface $fileFactory,
        UserContext $userContext,
        FileController $fileController
    ) {
        $this->assetRepository        = $assetRepository;
        $this->referenceRepository    = $referenceRepository;
        $this->variationRepository    = $variationRepository;
        $this->metadataRepository     = $metadataRepository;
        $this->localeRepository       = $localeRepository;
        $this->channelRepository      = $channelRepository;
        $this->variationFileGenerator = $variationFileGenerator;
        $this->assetFilesUpdater      = $assetFilesUpdater;
        $this->assetSaver             = $assetSaver;
        $this->referenceSaver         = $referenceSaver;
        $this->variationSaver         = $variationSaver;
        $this->assetRemover           = $assetRemover;
        $this->eventDispatcher        = $eventDispatcher;
        $this->assetFactory           = $assetFactory;
        $this->fileFactory            = $fileFactory;
        $this->userContext            = $userContext;
        $this->fileController         = $fileController;
    }

    /**
     * List of assets
     *
     * @Template
     * @AclAncestor("pimee_product_asset_index")
     *
     * @return array|RedirectResponse
     */
    public function indexAction()
    {
        try {
            $this->userContext->getAccessibleUserTree();
        } catch (\LogicException $e) {
            $this->addFlash('error', 'category.permissions.no_access_to_assets');

            return $this->redirectToRoute('oro_default');
        }

        return [
            'locales'    => $this->getUserLocales(),
            'dataLocale' => $this->getDataLocale(),
        ];
    }

    /**
     * View an asset
     *
     * @Template
     * @AclAncestor("pimee_product_asset_index")
     *
     * @param int|string $id
     *
     * @throws AccessDeniedException()
     *
     * @return array
     */
    public function viewAction($id)
    {
        $productAsset = $this->findProductAssetOr404($id);

        $isViewAssetGranted = $this->isGranted(Attributes::VIEW, $productAsset);
        if (!$isViewAssetGranted) {
            throw new AccessDeniedException();
        }

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
     * Create an asset
     *
     * @Template
     * @AclAncestor("pimee_product_asset_create")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('pimee_product_asset_index'));
        }

        $form = $this->createForm('pimee_product_asset_create');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $uploadedFile = $form->get('reference_file')->get('uploadedFile')->getData();
            $isLocalized  = $form->get('isLocalized')->getData();

            $asset = $this->assetFactory->create($isLocalized);
            $asset->setCode($form->get('code')->getData());

            try {
                if (!$isLocalized && null !== $uploadedFile) {
                    $reference = $asset->getReference();
                    $file = $this->fileFactory->createFromRawFile(
                        $uploadedFile,
                        ['path' => '', 'file_name' => '', 'guid' => ''],
                        FileStorage::ASSET_STORAGE_ALIAS
                    );
                    $file->setUploadedFile($uploadedFile);
                    $reference->setFile($file);
                    $this->assetFilesUpdater->updateAssetFiles($asset);
                    $this->assetSaver->save($asset);
                    $event = $this->eventDispatcher->dispatch(
                        AssetEvent::POST_UPLOAD_FILES,
                        new AssetEvent($asset)
                    );
                    $this->handleGenerationEventResult($event);
                } else {
                    $this->assetSaver->save($asset);
                }

                $this->addFlashMessage('success', 'pimee_product_asset.enrich_asset.flash.create.success');

                $route  = 'pimee_product_asset_edit';
                $params = ['id' => $asset->getId()];
            } catch (\Exception $e) {
                $this->addFlashMessage('error', 'pimee_product_asset.enrich_asset.flash.create.error');

                $route  = 'pimee_product_asset_index';
                $params = [];
            }

            return $this->redirect($this->generateUrl($route, $params));
        } elseif (!$form->isValid() && $form->isSubmitted()) {
            $uploadedFile = $form->get('reference_file')->get('uploadedFile');
            $errors = $uploadedFile->getErrors();
            if (!empty($errors)) {
                $message = '';
                foreach ($errors as $error) {
                    $message .= $error->getMessage() . ' ';
                }
                $this->addFlashMessage('error', $message);
            } else {
                $this->addFlashMessage('error', 'pimee_product_asset.enrich_asset.flash.create.error');
            }

            return $this->redirect($this->generateUrl('pimee_product_asset_index'));
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @return JsonResponse
     */
    public function getNextAvailableCodeAction(Request $request, $code)
    {
        $codes = $this->assetRepository->findSimilarCodes($code);

        if (!empty($codes)) {
            $nextId = 1;
            $code = substr($code, 0, strlen($code));
            while (in_array($code . '_' . $nextId, $codes)) {
                $nextId++;
            }

            return new JsonResponse(['nextCode' => sprintf('%s_%d', $code, $nextId)]);
        }

        return new JsonResponse();
    }

    /**
     * Edit an asset
     *
     * @AclAncestor("pimee_product_asset_index")
     * @Template()
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        if (!$this->isGranted(Attributes::EDIT, $productAsset)) {
            if ($this->isGranted(Attributes::VIEW, $productAsset)) {
                $parameters = $this->viewAction($id);

                return $this->render('PimEnterpriseProductAssetBundle:ProductAsset:view.html.twig', $parameters);
            }

            throw new AccessDeniedException();
        }

        $assetLocales = $productAsset->getLocales();

        if (null !== $request->get('locale')) {
            $locale = $assetLocales[$request->get('locale')];
        } elseif (!empty($assetLocales)) {
            $locale = reset($assetLocales);
        } else {
            $locale = null;
        }

        $assetForm = $this->createForm('pimee_product_asset', $productAsset);
        $assetForm->handleRequest($request);

        if ($assetForm->isValid()) {
            try {
                $this->assetFilesUpdater->updateAssetFiles($productAsset);
                $this->assetSaver->save($productAsset, ['schedule' => true]);
                $event = $this->eventDispatcher->dispatch(
                    AssetEvent::POST_UPLOAD_FILES,
                    new AssetEvent($productAsset)
                );
                $this->handleGenerationEventResult($event);
                $this->addFlashMessage('success', 'pimee_product_asset.enrich_asset.flash.update.success');
            } catch (\Exception $e) {
                $this->addFlashMessage('error', 'pimee_product_asset.enrich_asset.flash.update.error');
            }

            return $this->redirectAfterEdit($request, ['id' => $id]);
        } elseif ($assetForm->isSubmitted()) {
            $this->addFlashMessage('error', 'pimee_product_asset.enrich_asset.flash.update.error');
            // TODO find a better way
            $this->assetFilesUpdater->resetAllUploadedFiles($productAsset);
        }

        $metadata = null;
        if (null !== $productAsset) {
            $metadata = $this->getAssetMetadata($productAsset);
        }

        return [
            'asset'         => $productAsset,
            'form'          => $assetForm->createView(),
            'metadata'      => $metadata,
            'currentLocale' => $locale,
        ];
    }

    /**
     * Remove an asset
     *
     * @param Request    $request
     * @param string|int $id
     *
     * @return Response|RedirectResponse
     */
    public function removeAction(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        if (!$this->isGranted(Attributes::EDIT, $productAsset)) {
            throw new AccessDeniedException();
        }

        $this->assetRemover->remove($productAsset);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pimee_product_asset_index'));
        }
    }

    /**
     * Delete a reference file and redirect
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @return RedirectResponse
     */
    public function deleteReferenceFileAction(Request $request, $id)
    {
        $reference = $this->findReferenceOr404($id);
        if (!$this->isGranted(Attributes::EDIT, $reference->getAsset())) {
            throw new AccessDeniedException();
        }

        $asset = $reference->getAsset();

        try {
            $this->assetFilesUpdater->deleteReferenceFile($reference);
            $this->referenceSaver->save($reference, ['schedule' => false]);
            $this->addFlashMessage('success', 'pimee_product_asset.enrich_reference.flash.delete.success');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'pimee_product_asset.enrich_reference.flash.delete.error');
        }

        $parameters = ['id' => $asset->getId()];

        if (null !== $reference->getLocale()) {
            $parameters['locale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * Delete a variation file and redirect
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @return RedirectResponse
     */
    public function deleteVariationFileAction(Request $request, $id)
    {
        $variation = $this->findVariationOr404($id);
        if (!$this->isGranted(Attributes::EDIT, $variation->getAsset())) {
            throw new AccessDeniedException();
        }

        $asset     = $variation->getAsset();
        $reference = $variation->getReference();

        try {
            $this->assetFilesUpdater->deleteVariationFile($variation);
            $this->variationSaver->save($variation, ['schedule' => true]);
            $this->addFlashMessage('success', 'pimee_product_asset.enrich_variation.flash.delete.success');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'pimee_product_asset.enrich_variation.flash.delete.error');
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
     * @param int|string $id
     *
     * @return RedirectResponse
     */
    public function resetVariationFileAction(Request $request, $id)
    {
        $variation = $this->findVariationOr404($id);
        if (!$this->isGranted(Attributes::EDIT, $variation->getAsset())) {
            throw new AccessDeniedException();
        }

        $asset = $variation->getAsset();
        $reference = $variation->getReference();

        try {
            $this->assetFilesUpdater->resetVariationFile($variation);
            $this->variationSaver->save($variation, ['schedule' => true]);
            $event = $this->eventDispatcher->dispatch(
                AssetEvent::POST_UPLOAD_FILES,
                new AssetEvent($asset)
            );
            $this->handleGenerationEventResult($event);
            $this->addFlashMessage('success', 'pimee_product_asset.enrich_asset.flash.update.success');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'pimee_product_asset.enrich_asset.flash.update.error');
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
     * @param int|string $id
     *
     * @return RedirectResponse
     */
    public function resetVariationsFilesAction(Request $request, $id)
    {
        $reference = $this->findReferenceOr404($id);
        if (!$this->isGranted(Attributes::EDIT, $reference->getAsset())) {
            throw new AccessDeniedException();
        }

        $asset = $reference->getAsset();

        try {
            $this->assetFilesUpdater->resetAllVariationsFiles($reference, true);
            $this->assetSaver->save($asset, ['schedule' => true]);
            $event = $this->eventDispatcher->dispatch(
                AssetEvent::POST_UPLOAD_FILES,
                new AssetEvent($asset)
            );
            $this->handleGenerationEventResult($event);
            $this->addFlashMessage('success', 'pimee_product_asset.enrich_asset.flash.update.success');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'pimee_product_asset.enrich_asset.flash.update.error');
        }

        $parameters = ['id' => $asset->getId()];

        if (null !== $reference->getLocale()) {
            $parameters['locale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * Action to render the asset thumbnail depending on a channel (and a locale if the asset is localizable).
     *
     * @see PimEnterprise\Component\ProductAsset\Model\AssetInterface::getFileForContext()
     *
     * @param Request $request
     * @param string  $code
     * @param string  $filter
     * @param string  $channelCode
     * @param string  $localeCode
     *
     * @return RedirectResponse
     */
    public function thumbnailAction(Request $request, $code, $filter, $channelCode, $localeCode = null)
    {
        $asset    = $this->findProductAssetByCodeOr404($code);
        $filename = FileController::DEFAULT_IMAGE_KEY;

        if (null !== $channel = $this->channelRepository->findOneByIdentifier($channelCode)) {
            $locale = null;
            if (null !== $localeCode) {
                $locale = $this->localeRepository->findOneByIdentifier($localeCode);
            }

            if (null !== $file = $asset->getFileForContext($channel, $locale)) {
                $filename = $file->getKey();
            }
        }

        return $this->fileController->showAction($request, urlencode($filename), $filter);
    }

    /**
     * Assets mass upload page
     *
     * @return Response
     */
    public function massUploadAction()
    {
        return $this->render('PimEnterpriseProductAssetBundle:ProductAsset:mass-upload.html.twig');
    }

    /**
     * @param AssetEvent $event
     */
    protected function handleGenerationEventResult(AssetEvent $event)
    {
        $items = $event->getProcessedList();

        if ($items->hasItemInState(ProcessedItem::STATE_ERROR)) {
            foreach ($items->getItemsInState(ProcessedItem::STATE_ERROR) as $item) {
                $flashParameters = ['%channel%' => $item->getItem()->getChannel()->getCode()];
                switch (true) {
                    case $item->getException() instanceof InvalidOptionsTransformationException:
                        $flash = 'pimee_product_asset.enrich_variation.flash.transformation.invalid_options';
                        break;
                    case $item->getException() instanceof ImageWidthException:
                        $flash = 'pimee_product_asset.enrich_variation.flash.transformation.image_width_error';
                        break;
                    case $item->getException() instanceof ImageHeightException:
                        $flash = 'pimee_product_asset.enrich_variation.flash.transformation.image_height_error';
                        break;
                    case $item->getException() instanceof GenericTransformationException:
                        $flash = 'pimee_product_asset.enrich_variation.flash.transformation.not_applicable';
                        break;
                    case $item->getException() instanceof NonRegisteredTransformationException:
                        $flash = 'pimee_product_asset.enrich_variation.flash.transformation.non_registered';
                        $flashParameters['%transformation%'] = $item->getException()->getTransformation();
                        $flashParameters['%mimeType%'] = $item->getException()->getMimeType();
                        break;
                    default:
                        $flash = 'pimee_product_asset.enrich_variation.flash.transformation.error';
                        break;
                }
                $this->addFlashMessage('error', $flash, $flashParameters);
            }
        } elseif ($items->hasItemInState(ProcessedItem::STATE_SUCCESS)) {
            $this->addFlashMessage('success', 'pimee_product_asset.enrich_variation.flash.transformation.success');
        }
    }

    /**
     * Dispatch to asset view or asset edit when a user click on an asset grid row
     *
     * @AclAncestor("pimee_product_asset_index")
     *
     * @param Request $request
     * @param int     $id
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function dispatchAction(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        if ($this->isGranted(Attributes::EDIT, $productAsset)) {
            $edit = $this->editAction($request, $id);

            return $this->render('PimEnterpriseProductAssetBundle:ProductAsset:edit.html.twig', $edit);
        }

        if ($this->isGranted(Attributes::VIEW, $productAsset)) {
            $view = $this->viewAction($id);

            return $this->render('PimEnterpriseProductAssetBundle:ProductAsset:view.html.twig', $view);
        }

        throw new AccessDeniedException();
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
     * Set flash message
     *
     * @param string  $type       the flash type
     * @param string  $message    the flash message
     * @param array   $parameters the flash message parameters
     */
    protected function addFlashMessage($type, $message, array $parameters = [])
    {
        $this->container->get('session')->getFlashBag()->add($type, new Message($message, $parameters));
    }

    /**
     * Switch case to redirect after saving a product asset from the edit form
     *
     * @param Request $request
     * @param array   $params  Request parameters
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
                sprintf('Product asset with id "%s" cannot be found.', (string) $id)
            );
        }

        return $productAsset;
    }

    /**
     * Find an Asset by its code or return a 404 response
     *
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return AssetInterface
     */
    protected function findProductAssetByCodeOr404($code)
    {
        $productAsset = $this->assetRepository->findOneByIdentifier($code);

        if (null === $productAsset) {
            throw $this->createNotFoundException(
                sprintf('Product asset with code "%s" cannot be found.', $code)
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
    protected function findReferenceOr404($id)
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
    protected function findVariationOr404($id)
    {
        $variation = $this->variationRepository->find($id);

        if (null === $variation) {
            throw $this->createNotFoundException(
                sprintf('Asset variation with id "%s" could not be found.', (string) $id)
            );
        }

        return $variation;
    }

    /**
     * @return LocaleInterface[]
     */
    protected function getUserLocales()
    {
        return $this->userContext->getUserLocales();
    }

    /**
     * @return LocaleInterface
     */
    protected function getDataLocale()
    {
        return $this->userContext->getCurrentLocale();
    }
}
