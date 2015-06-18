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
use Akeneo\Component\FileStorage\RawFile\RawFileStorerInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Type\UploadType;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\Reference;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\Variation;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\ProductAssetFileSystems;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\FileMetadataRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ReferenceRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\VariationRepositoryInterface;
use PimEnterprise\Component\ProductAsset\VariationFileGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Asset controller
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
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

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var VariationFileGeneratorInterface */
    protected $variationFileGenerator;

    /** @var SaverInterface */
    protected $assetSaver;

    /**
     * @param AssetRepositoryInterface        $assetRepository
     * @param ReferenceRepositoryInterface    $referenceRepository
     * @param VariationRepositoryInterface    $variationRepository
     * @param FileMetadataRepositoryInterface $metadataRepository
     * @param ChannelRepositoryInterface      $channelRepository
     * @param RawFileStorerInterface          $rawFileStorer
     * @param VariationFileGeneratorInterface $variationFileGenerator
     * @param SaverInterface                  $assetSaver
     * @param EventDispatcherInterface        $eventDispatcher
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        ReferenceRepositoryInterface $referenceRepository,
        VariationRepositoryInterface $variationRepository,
        FileMetadataRepositoryInterface $metadataRepository,
        ChannelRepositoryInterface $channelRepository,
        RawFileStorerInterface $rawFileStorer,
        VariationFileGeneratorInterface $variationFileGenerator,
        SaverInterface $assetSaver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->assetRepository        = $assetRepository;
        $this->referenceRepository    = $referenceRepository;
        $this->variationRepository    = $variationRepository;
        $this->metadataRepository     = $metadataRepository;
        $this->channelRepository      = $channelRepository;
        $this->rawFileStorer          = $rawFileStorer;
        $this->variationFileGenerator = $variationFileGenerator;
        $this->assetSaver             = $assetSaver;
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
     * @Template
     * @AclAncestor("pimee_product_asset_index")
     *
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        $assetForm    = $this->createForm('pimee_product_asset', $productAsset)->createView();

        $metadata = null;
        if (null !== $productAsset) {
            $metadata = $this->getAssetMetadata($productAsset);
        }

        return [
            'asset'    => $productAsset,
            'form'     => $assetForm,
            'metadata' => $metadata
        ];
    }

    /**
     * Update a product asset and redirect
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @return RedirectResponse
     */
    public function updateAction(Request $request, $id)
    {
        $asset = $this->findProductAssetOr404($id);
//        $assetWithoutFiles = $this->buildAssetWithoutFiles($asset);
        $form = $this->createForm('pimee_product_asset', $asset);

        $form->handleRequest($request);

        // TODO: check if references and variations are really validated
        if ($form->isValid()) {
            try {
                $this->handleAssetFiles($asset);
                $this->assetSaver->save($asset);
                $this->addFlash($request, 'success', 'pimee_product_asset.enrich_asset.flash.update.success');
            } catch (\Exception $e) {
                $this->addFlash($request, 'error', 'pimee_product_asset.enrich_asset.flash.update.error');
            }
        }

        return $this->redirectAfterEdit($request, ['id' => $id]);
    }

    /**
     * @param Request    $request
     * @param int|string $assetId
     * @param int|string $id
     *
     * @return RedirectResponse
     *
     * TODO: delete this
     */
    public function uploadReferenceAction(Request $request, $assetId, $id)
    {
        $reference = $this->referenceRepository->find($id);

        if ($request->isMethod('POST')) {
            $form = $this->createUploadForm();
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->get('file')->getData();
                if (null !== $clientFile = $data->getFile()) {
                    // TODO: Generate Metadata
                    $uploadedFile = $this->rawFileStorer->store($clientFile, ProductAssetFileSystems::FS_STORAGE);
                    $reference->setFile($uploadedFile);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($reference);
                    $em->flush();
                }
            }
        }

        return $this->redirect($this->generateUrl('pimee_product_asset_edit', ['id' => $assetId]));
    }

    /**
     * @param Request    $request
     * @param int|string $assetId
     * @param int|string $id
     *
     * @return RedirectResponse
     *
     * TODO: delete this
     */
    public function uploadVariationAction(Request $request, $assetId, $id)
    {
        /** @var VariationInterface $variation */
        $variation = $this->variationRepository->find($id);

        if ($request->isMethod('POST')) {
            $form = $this->createUploadForm();
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->get('file')->getData();
                if (null !== $clientFile = $data->getFile()) {
                    $uploadedFile = $this->rawFileStorer->store($clientFile, ProductAssetFileSystems::FS_STORAGE);
                    $variation->setFile($uploadedFile);
                    $variation->setLocked(true);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($variation);
                    $em->flush();

                    // TODO: problem with this method is that 2 files are stored in
                    // the VFS but only the previous one is used
                    // maybe when we change the file of the variation, the other one should be softdeleted
                    /*
                    $this->launchVariationFileGeneration(
                        $variation->getReference()->getAsset(),
                        $variation->getChannel(),
                        $variation->getReference()->getLocale()
                    );
                    */
                }
            }
        }

        return $this->redirect($this->generateUrl('pimee_product_asset_edit', ['id' => $assetId]));
    }

    /**
     * TODO: dedicated service and clean
     *
     * @param AssetInterface $asset
     *
     * @return Asset
     */
    protected function buildAssetWithoutFiles(AssetInterface $asset)
    {
        // TODO: do not hardcode the class
        $emptyAsset = new Asset();
        foreach ($asset->getReferences() as $reference) {
            // TODO: do not hardcode the class
            $emptyReference = new Reference();
            if (null !== $locale = $reference->getLocale()) {
                $emptyReference->setLocale($locale);
            }

            /** @var VariationInterface $variation */
            foreach ($reference->getVariations() as $variation) {
                $emptyVariation = new Variation();
                if (null !== $channel = $variation->getChannel()) {
                    $emptyVariation->setChannel($channel);
                }

                $emptyReference->addVariation($emptyVariation);
            }

            $emptyAsset->addReference($emptyReference);
        }

        return $emptyAsset;
    }

    /**
     * TODO: dedicated service and clean
     *
     * @param AssetInterface $asset
     */
    protected function handleAssetFiles(AssetInterface $asset)
    {
        foreach ($asset->getReferences() as $reference) {
            foreach ($reference->getVariations() as $variation) {
                if (null !== $uploadedFile = $variation->getSourceFile()->getUploadedFile()) {
                    $file = $this->rawFileStorer->store($uploadedFile, ProductAssetFileSystems::FS_STORAGE);
                    $variation->setSourceFile($file);
                    $variation->setFile(null);
                    $variation->setLocked(true);
                    //TODO: dispatch event to be able to launch command "pim:asset:generate-variation"
                }
                if (null !== $variation->getFile() && null === $variation->getFile()->getId()) {
                    $variation->setFile(null);
                }
                if (null !== $variation->getSourceFile() && null === $variation->getSourceFile()->getId()) {
                    $variation->setSourceFile(null);
                }
            }

            if (null !== $uploadedFile = $reference->getFile()->getUploadedFile()) {
                $file = $this->rawFileStorer->store($uploadedFile, ProductAssetFileSystems::FS_STORAGE);
                $reference->setFile($file);
                //TODO: dispatch event to be able to launch command "pim:asset:generate-variations-from-reference"
            }
            if (null !== $reference->getFile() && null === $reference->getFile()->getId()) {
                $reference->setFile(null);
            }
        }
    }

    /**
     * TODO: Full method may be removed for the update variation card PIM-4073
     *
     * @param AssetInterface $productAsset
     *
     * @return array
     */
    protected function createAttachments(AssetInterface $productAsset)
    {
        $channels   = $this->channelRepository->getFullChannels();
        $references = $productAsset->getReferences();

        $attachments = [];
        foreach ($references as $refKey => $reference) {
            $attachments[$refKey]['reference'] = $reference;

            $refFormView = $this->createUploadForm()->createView();
            $refFormView->children['file']->vars['form']->children['file']->vars['id'] = sprintf(
                'ref_%s',
                $reference->getId()
            );

            $attachments[$refKey]['uploadForm'] = $refFormView;

            foreach ($channels as $channel) {
                $variation = $reference->getVariation($channel);
                if (null !== $variation) {
                    $channelCode = $channel->getCode();

                    $metadata = null;
                    if (null !== $variation->getFile()) {
                        $metadata = $this->metadataRepository->findOneBy(
                            [
                                'file' => $variation->getFile()->getId()
                            ]
                        );
                    }
                    $varFormView = $this->createUploadForm()->createView();
                    $varFormView->children['file']->vars['form']->children['file']->vars['id'] = sprintf(
                        'ref_%s_var_%s',
                        $reference->getId(),
                        $variation->getId()
                    );

                    $attachments[$refKey]['variations'][$channelCode] = [
                        'entity'     => $variation,
                        'metadata'   => $metadata,
                        'uploadForm' => $varFormView
                    ];
                }
            }
        }

        return $attachments;
    }

    /**
     * @return array
     */
    protected function getAssetMetadata(AssetInterface $productAsset)
    {
        $metadata = [];

        foreach ($productAsset->getReferences() as $reference) {
            /** @var ReferenceInterface $reference */
            $metadata['references'][$reference->getId()] = $reference->getFile() ? $this->getFileMetadata($reference->getFile()) : null;
            foreach ($reference->getVariations() as $variation) {
                $metadata['variations'][$variation->getId()] = $variation->getFile() ? $this->getFileMetadata($variation->getFile()) : null;
            }
        }

        return $metadata;
    }

    /**
     * @param FileInterface $file
     *
     * @return array
     */
    protected function getFileMetadata(FileInterface $file)
    {
        $metadata = $this->metadataRepository->findOneBy(['file' => $file->getId()]);

        return $metadata;
    }

    /**
     * TODO: This one is only used for attachments, may be removed too
     *
     * @return Form
     */
    protected function createUploadForm()
    {
        return $this->createForm(new UploadType());
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
     * @param array   $params
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
     * @return AssetInterface
     *
     * @throws NotFoundHttpException
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
     * @param AssetInterface    $asset
     * @param ChannelInterface  $channel
     * @param LocaleInterface   $locale
     *
     * @throws \Exception
     */
    protected function launchVariationFileGeneration(
        AssetInterface $asset,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $pathFinder = new PhpExecutableFinder();
        $cmd = sprintf(
            '%s %s/console pim:asset:generate-variation %s %s %s',
            $pathFinder->find(),
            $rootDir,
            $asset->getCode(),
            $channel->getCode(),
            null !== $locale ? $locale->getCode() : ''
        );

        exec($cmd . ' &');
    }
}
