<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Controller;

use Akeneo\Asset\Bundle\Event\AssetEvent;
use Akeneo\Asset\Bundle\Event\VariationHasBeenDeleted;
use Akeneo\Asset\Bundle\Form\Type\AssetType;
use Akeneo\Asset\Bundle\Form\Type\CreateAssetType;
use Akeneo\Asset\Component\Builder\ReferenceBuilderInterface;
use Akeneo\Asset\Component\Builder\VariationBuilderInterface;
use Akeneo\Asset\Component\Factory\AssetFactory;
use Akeneo\Asset\Component\FileStorage;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\FileMetadataInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\Repository\AssetCategoryRepositoryInterface;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Asset\Component\Repository\FileMetadataRepositoryInterface;
use Akeneo\Asset\Component\Repository\ReferenceRepositoryInterface;
use Akeneo\Asset\Component\Repository\VariationRepositoryInterface;
use Akeneo\Asset\Component\Updater\FilesUpdaterInterface;
use Akeneo\Asset\Component\VariationFileGeneratorInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Controller\Ui\FileController;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\CategoryManager;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Platform\Bundle\UIBundle\Flash\Message;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use Akeneo\Tool\Component\FileTransformer\Exception\NonRegisteredTransformationException;
use Akeneo\Tool\Component\FileTransformer\Exception\NotApplicableTransformation\GenericTransformationException;
use Akeneo\Tool\Component\FileTransformer\Exception\NotApplicableTransformation\ImageHeightException;
use Akeneo\Tool\Component\FileTransformer\Exception\NotApplicableTransformation\ImageWidthException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
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
    private const DEFAULT_IMAGE_PATH = '/bundles/pimui/images/Default-picture.svg';

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

    /** @var FileInfoFactoryInterface */
    protected $fileInfoFactory;

    /** @var UserContext */
    protected $userContext;

    /** @var FileController */
    protected $fileController;

    /** @var AssetCategoryRepositoryInterface */
    protected $assetCategoryRepo;

    /** @var CategoryRepositoryInterface */
    protected $categoryRepository;

    /** @var CategoryManager */
    protected $categoryManager;

    /** @var ReferenceBuilderInterface */
    protected $referenceBuilder;

    /** @var VariationBuilderInterface */
    protected $variationBuilder;

    /**
     * @param AssetRepositoryInterface         $assetRepository
     * @param ReferenceRepositoryInterface     $referenceRepository
     * @param VariationRepositoryInterface     $variationRepository
     * @param FileMetadataRepositoryInterface  $metadataRepository
     * @param LocaleRepositoryInterface        $localeRepository
     * @param ChannelRepositoryInterface       $channelRepository
     * @param VariationFileGeneratorInterface  $variationFileGenerator
     * @param FilesUpdaterInterface            $assetFilesUpdater
     * @param SaverInterface                   $assetSaver
     * @param SaverInterface                   $referenceSaver
     * @param SaverInterface                   $variationSaver
     * @param RemoverInterface                 $assetRemover
     * @param EventDispatcherInterface         $eventDispatcher
     * @param AssetFactory                     $assetFactory
     * @param FileInfoFactoryInterface         $fileInfoFactory
     * @param UserContext                      $userContext
     * @param FileController                   $fileController
     * @param AssetCategoryRepositoryInterface $assetCategoryRepo
     * @param CategoryRepositoryInterface      $categoryRepository
     * @param CategoryManager                  $categoryManager
     * @param ReferenceBuilderInterface        $referenceBuilder
     * @param VariationBuilderInterface        $variationBuilder
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
        FileInfoFactoryInterface $fileInfoFactory,
        UserContext $userContext,
        FileController $fileController,
        AssetCategoryRepositoryInterface $assetCategoryRepo,
        CategoryRepositoryInterface $categoryRepository,
        CategoryManager $categoryManager,
        ReferenceBuilderInterface $referenceBuilder,
        VariationBuilderInterface $variationBuilder
    ) {
        $this->assetRepository = $assetRepository;
        $this->referenceRepository = $referenceRepository;
        $this->variationRepository = $variationRepository;
        $this->metadataRepository = $metadataRepository;
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->variationFileGenerator = $variationFileGenerator;
        $this->assetFilesUpdater = $assetFilesUpdater;
        $this->assetSaver = $assetSaver;
        $this->referenceSaver = $referenceSaver;
        $this->variationSaver = $variationSaver;
        $this->assetRemover = $assetRemover;
        $this->eventDispatcher = $eventDispatcher;
        $this->assetFactory = $assetFactory;
        $this->fileInfoFactory = $fileInfoFactory;
        $this->userContext = $userContext;
        $this->fileController = $fileController;
        $this->assetCategoryRepo = $assetCategoryRepo;
        $this->categoryRepository = $categoryRepository;
        $this->categoryManager = $categoryManager;
        $this->referenceBuilder = $referenceBuilder;
        $this->variationBuilder = $variationBuilder;
    }

    /**
     * Create an asset
     *
     * @Template
     * @AclAncestor("pimee_product_asset_create")
     *
     * @param Request $request
     *
     * @return array|JsonResponse
     */
    public function createAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('pimee_product_asset_index'));
        }

        $form = $this->createForm(CreateAssetType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $uploadedFile = $form->get('reference_file')->get('uploadedFile')->getData();
            $isLocalized = $form->get('isLocalized')->getData();

            $asset = $this->assetFactory->create();
            $asset->setCode($form->get('code')->getData());
            $this->assetFactory->createReferences($asset, $isLocalized);

            try {
                if (!$isLocalized && null !== $uploadedFile) {
                    $reference = $asset->getReference();
                    $file = $this->fileInfoFactory->createFromRawFile(
                        $uploadedFile,
                        FileStorage::ASSET_STORAGE_ALIAS
                    );
                    $file->setUploadedFile($uploadedFile);
                    $reference->setFileInfo($file);

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

                $route = 'pimee_product_asset_edit';
                $params = ['id' => $asset->getId()];
            } catch (\Exception $e) {
                $this->addFlashMessage('error', 'pimee_product_asset.enrich_asset.flash.create.error');

                $route = 'pimee_product_asset_index';
                $params = [];
            }

            if ($asset->isLocalizable()) {
                $params['dataLocale'] = $this->getDataLocale()->getCode();
            }

            return new JsonResponse(
                [
                    'route'  => $route,
                    'params' => $params,
                ]
            );
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

            return new JsonResponse(['route' => 'pimee_product_asset_index']);
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
        if (null === $this->assetRepository->findOneByCode($code)) {
            return new JsonResponse();
        }

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
     * Remove an asset
     *
     * @param int $id
     *
     * @return Response
     */
    public function removeAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $productAsset = $this->findProductAssetOr404($id);
        if (!$this->isGranted(Attributes::EDIT, $productAsset)) {
            throw new AccessDeniedException();
        }

        $this->assetRemover->remove($productAsset);

        return new Response('', 204);
    }

    /**
     * Delete a reference file and redirect
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
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
            $this->referenceSaver->save($reference);
            $this->addFlashMessage('success', 'pimee_product_asset.enrich_reference.flash.delete.success');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'pimee_product_asset.enrich_reference.flash.delete.error');
        }

        $parameters = ['id' => $asset->getId()];

        if (null !== $reference->getLocale()) {
            $parameters['dataLocale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * Delete a variation file and redirect
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     */
    public function deleteVariationFileAction(Request $request, $id)
    {
        $variation = $this->findVariationOr404($id);
        if (!$this->isGranted(Attributes::EDIT, $variation->getAsset())) {
            throw new AccessDeniedException();
        }

        $asset = $variation->getAsset();
        $reference = $variation->getReference();

        try {
            $this->assetFilesUpdater->deleteVariationFile($variation);
            $this->variationSaver->save($variation);

            $this->eventDispatcher->dispatch(
                VariationHasBeenDeleted::VARIATION_HAS_BEEN_DELETED,
                new VariationHasBeenDeleted($asset)
            );

            $this->addFlashMessage('success', 'pimee_product_asset.enrich_variation.flash.delete.success');
        } catch (\Exception $e) {
            $this->addFlashMessage('error', 'pimee_product_asset.enrich_variation.flash.delete.error');
        }

        $parameters = ['id' => $asset->getId()];

        if (null !== $reference->getLocale()) {
            $parameters['dataLocale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * Reset a variation file with the reference and redirect
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
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
            $this->variationSaver->save($variation);
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
            $parameters['dataLocale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * Reset all variation files with the reference file and redirect
     *
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
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
            $this->assetSaver->save($asset);
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
            $parameters['dataLocale'] = $reference->getLocale()->getCode();
        }

        return $this->redirectAfterEdit($request, $parameters);
    }

    /**
     * Dispatch to asset view or asset edit when a user click on an asset grid row
     *
     * @param Request $request
     * @param int     $id
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);

        // TODO merge 3.1: remove condition
        if (null !== $this->referenceBuilder && null !== $this->variationBuilder) {
            $this->referenceBuilder->buildMissingLocalized($productAsset);

            $variations = array_reduce($productAsset->getReferences()->toArray(), function ($carry, ReferenceInterface $reference) {
                $missings = $this->variationBuilder->buildMissing($reference);

                return $missings !== null ? $carry + $missings : $carry;
            }, []);

            if (count($variations) > 0) {
                $this->assetSaver->save($productAsset);
            }
        }

        if ($this->isGranted(Attributes::EDIT, $productAsset)) {
            return $this->edit($request, $id);
        }

        if ($this->isGranted(Attributes::VIEW, $productAsset)) {
            return $this->view($id);
        }

        throw new AccessDeniedException();
    }

    /**
     * List categories associated with the provided asset and descending from the category
     * defined by the parent parameter.
     *
     * @AclAncestor("pimee_product_asset_categories_view")
     *
     * @Template("AkeneoAssetBundle:ProductAsset:list-categories.json.twig")
     *
     * @param Request $request    The request object
     * @param int     $id         Asset id
     * @param int     $categoryId The parent category id
     *
     * @throws NotFoundHttpException
     *
     * @return array
     */
    public function listCategoriesAction(Request $request, $id, $categoryId)
    {
        $parent = $this->categoryRepository->find($categoryId);
        if (null === $parent) {
            throw new NotFoundHttpException(sprintf('Category %d not found', $categoryId));
        }

        $selectedCategoryIds = $request->get('selected');
        $categories = null;
        if (null !== $selectedCategoryIds) {
            $categories = $this->categoryRepository->getCategoriesByIds($selectedCategoryIds);
        } elseif (null !== $asset = $this->findProductAssetOr404($id)) {
            $categories = $asset->getCategories();
        }

        $trees = $this->categoryManager->getGrantedFilledTree($parent, $categories);

        return [
            'trees'      => $trees,
            'categories' => $categories
        ];
    }

    /**
     * Action to render the asset thumbnail depending on a channel (and a locale if the asset is localizable).
     *
     * @see \Akeneo\Asset\Component\Model\AssetInterface::getFileForContext()
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
        return $this->fileController->showAction(
            $request,
            urlencode($this->getFileName($code, $channelCode, $localeCode)),
            $filter
        );
    }

    /**
     * @param Request $request
     * @param string  $code
     * @param string  $channelCode
     * @param string  $localeCode
     *
     * @return Response
     */
    public function originalAction(Request $request, $code, $channelCode, $localeCode = null): Response
    {
        $filename = $this->getFileName($code, $channelCode, $localeCode);
        if (FileController::DEFAULT_IMAGE_KEY === $filename) {
            return new RedirectResponse(self::DEFAULT_IMAGE_PATH, 301);
        }

        return $this->fileController->downloadAction(urlencode($filename));
    }

    /**
     * @param string $code
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return string
     */
    private function getFileName($code, $channelCode, $localeCode = null): string
    {
        $asset = $this->findProductAssetByCodeOr404($code);
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

        return $filename;
    }

    /**
     * Assets mass upload page
     *
     * @AclAncestor("pimee_product_asset_mass_upload")
     *
     * @return Response
     */
    public function massUploadAction()
    {
        return $this->render('AkeneoAssetBundle:ProductAsset:mass-upload.html.twig');
    }

    /**
     * @param AssetEvent $event
     */
    protected function handleGenerationEventResult(AssetEvent $event)
    {
        $items = $event->getProcessedList();

        if ($items->hasItemInState(ProcessedItem::STATE_ERROR)) {
            foreach ($items->getItemsInState(ProcessedItem::STATE_ERROR) as $item) {
                if (!$this->canVariationBeGeneratedForMimeType($item->getItem())) {
                    continue;
                }

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
                    case $item->getException() instanceof MissingAssetTransformationForChannelException:
                        $flash = 'pimee_product_asset.enrich_variation.flash.transformation.missing_asset_transformation_for_channel';
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
     * @param mixed $item
     *
     * @return bool
     */
    protected function canVariationBeGeneratedForMimeType($item)
    {
        $supportedMimeTypes = [
            'image/jpeg',
            'image/tiff',
            'image/png',
        ];

        return $item instanceof VariationInterface
            && null !== $item->getReference()->getFileInfo()
            && in_array($item->getReference()->getFileInfo()->getMimeType(), $supportedMimeTypes);
    }

    /**
     * Edit an asset
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    protected function edit(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        $assetLocales = $productAsset->getLocales();

        if (null !== $request->get('dataLocale') && $productAsset->isLocalizable()) {
            $locale = $assetLocales[$request->get('dataLocale')];
        } elseif (!empty($assetLocales)) {
            $locale = reset($assetLocales);
        } else {
            $locale = null;
        }

        $assetForm = $this->createForm(AssetType::class, $productAsset);
        $assetForm->handleRequest($request);

        if ($assetForm->isValid()) {
            try {
                $this->assetFilesUpdater->updateAssetFiles($productAsset);
                $this->assetSaver->save($productAsset);

                if ($request->files->count() > 0) {
                    $event = $this->eventDispatcher->dispatch(
                        AssetEvent::POST_UPLOAD_FILES,
                        new AssetEvent($productAsset)
                    );
                    $this->handleGenerationEventResult($event);
                }

                $this->addFlashMessage('success', 'pimee_product_asset.enrich_asset.flash.update.success');
            } catch (\Exception $e) {
                $this->addFlashMessage('error', 'pimee_product_asset.enrich_asset.flash.update.error');
            }

            return $this->redirectAfterEdit($request, ['id' => $id]);
        } elseif ($assetForm->isSubmitted()) {
            foreach ($assetForm->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            $this->assetFilesUpdater->resetAllUploadedFiles($productAsset);
        }

        $trees = $this->assetCategoryRepo->getItemCountByGrantedTree($productAsset, $this->userContext->getUser());

        return $this->render('AkeneoAssetBundle:ProductAsset:edit.html.twig', [
            'asset'         => $productAsset,
            'form'          => $assetForm->createView(),
            'metadata'      => $this->getAssetMetadata($productAsset),
            'currentLocale' => $locale,
            'trees'         => $trees,
        ]);
    }

    /**
     * View an asset
     *
     * @param int|string $id
     *
     * @throws AccessDeniedException()
     *
     * @return array|Response
     */
    protected function view($id)
    {
        $productAsset = $this->findProductAssetOr404($id);

        $isViewAssetGranted = $this->isGranted(Attributes::VIEW, $productAsset);
        if (!$isViewAssetGranted) {
            throw new AccessDeniedException();
        }

        $references = $productAsset->getReferences();
        $attachments = [];
        foreach ($references as $refKey => $reference) {
            $attachments[$refKey]['reference'] = $reference;
        }

        return $this->render('AkeneoAssetBundle:ProductAsset:view.html.twig', [
            'asset'       => $productAsset,
            'attachments' => $attachments,
            'metadata'    => $this->getAssetMetadata($productAsset)
        ]);
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
            $referenceFileMeta = $reference->getFileInfo() ? $this->getFileMetadata($reference->getFileInfo()) : null;
            $metadata['references'][$reference->getId()] = $referenceFileMeta;

            foreach ($reference->getVariations() as $variation) {
                $variationFileMeta = $variation->getFileInfo() ?
                    $this->getFileMetadata($variation->getFileInfo()) :
                    null;
                $metadata['variations'][$variation->getId()] = $variationFileMeta;
            }
        }

        return $metadata;
    }

    /**
     * @param FileInfoInterface $fileInfo
     *
     * @return FileMetadataInterface
     */
    protected function getFileMetadata(FileInfoInterface $fileInfo)
    {
        $metadata = $this->metadataRepository->findOneBy(['fileInfo' => $fileInfo->getId()]);

        return $metadata;
    }

    /**
     * Set flash message
     *
     * @param string $type       the flash type
     * @param string $message    the flash message
     * @param array  $parameters the flash message parameters
     */
    protected function addFlashMessage($type, $message, array $parameters = [])
    {
        $message = new Message($message, $parameters);
        parent::addFlash($type, $message);
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
        if (null !== $request->get('dataLocale')) {
            $params['dataLocale'] = $request->get('dataLocale');
        }

        return new JsonResponse(
            [
                'route'  => 'pimee_product_asset_edit',
                'params' => $params,
            ]
        );
    }

    /**
     * Find an Asset by its id or return a 404 response
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return AssetInterface
     */
    protected function findProductAssetOr404($id)
    {
        $productAsset = $this->assetRepository->find($id);

        if (null === $productAsset) {
            throw new NotFoundHttpException(
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
            throw new NotFoundHttpException(
                sprintf('Product asset with code "%s" cannot be found.', $code)
            );
        }

        return $productAsset;
    }

    /**
     * Find a reference by its id or return a 404 response
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return ReferenceInterface
     */
    protected function findReferenceOr404($id)
    {
        $reference = $this->referenceRepository->find($id);

        if (null === $reference) {
            throw new NotFoundHttpException(
                sprintf('Asset reference with id "%s" could not be found.', (string) $id)
            );
        }

        return $reference;
    }

    /**
     * Find a variation by its id or return a 404 response
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return VariationInterface
     */
    protected function findVariationOr404($id)
    {
        $variation = $this->variationRepository->find($id);

        if (null === $variation) {
            throw new NotFoundHttpException(
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
