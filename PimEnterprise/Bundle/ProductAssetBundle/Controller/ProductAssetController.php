<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\EnrichBundle\Form\Type\UploadType;
use PimEnterprise\Component\ProductAsset\FileStorage\ProductAssetFileSystems;
use PimEnterprise\Component\ProductAsset\FileStorage\RawFile\RawFileStorerInterface;
use PimEnterprise\Component\ProductAsset\Repository\FileMetadataRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ProductAssetReferenceRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ProductAssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ProductAssetVariationRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Asset controller
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProductAssetController extends Controller
{
    /** @var ProductAssetRepositoryInterface */
    protected $assetRepository;

    /** @var FileMetadataRepositoryInterface */
    protected $metadataRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var ProductAssetReferenceRepositoryInterface */
    protected $referenceRepository;

    /** @var ProductAssetVariationRepositoryInterface */
    protected $variationRepository;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /**
     * @param ProductAssetRepositoryInterface          $assetRepository
     * @param ProductAssetReferenceRepositoryInterface $referenceRepository
     * @param ProductAssetVariationRepositoryInterface $variationRepository
     * @param FileMetadataRepositoryInterface          $metadataRepository
     * @param ChannelRepositoryInterface               $channelRepository
     * @param LocaleRepositoryInterface                $localeRepository
     * @param RawFileStorerInterface                   $rawFileStorer
     */
    public function __construct(
        ProductAssetRepositoryInterface $assetRepository,
        ProductAssetReferenceRepositoryInterface $referenceRepository,
        ProductAssetVariationRepositoryInterface $variationRepository,
        FileMetadataRepositoryInterface $metadataRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        RawFileStorerInterface $rawFileStorer
    ) {
        $this->assetRepository     = $assetRepository;
        $this->referenceRepository = $referenceRepository;
        $this->variationRepository = $variationRepository;
        $this->metadataRepository  = $metadataRepository;
        $this->channelRepository   = $channelRepository;
        $this->localeRepository    = $localeRepository;
        $this->rawFileStorer       = $rawFileStorer;
    }

    /**
     * List of assets
     *
     * @param Request $request
     *
     * TODO : AclAncestor("pimee_product_asset_index")
     * @Template
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * View an asset
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @Template
     * TODO: AclAncestor("pimee_product_asset_index")
     *
     * @return array
     */
    public function viewAction(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        $references   = $productAsset->getReferences();

        $attachments = [];
        foreach ($references as $refKey => $reference) {
            $attachments[$refKey]['reference'] = $reference;

            foreach ($reference->getVariations() as $variation) {
                $metadata = $this->metadataRepository->findOneBy(
                    [
                        'file' => $variation->getFile()->getId()
                    ]
                );

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
     * TODO: AclAncestor("pimee_product_asset_index")
     *
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        $channels     = $this->channelRepository->getFullChannels();
        $references   = $productAsset->getReferences();
        $locales      = $this->localeRepository->getActivatedLocales();

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
                $variation   = $reference->getVariation($channel);
                $channelCode = $channel->getCode();

                $metadata = null;
                if (null !== $variation) {
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
                    'entity'        => $variation,
                    'metadata'      => $metadata,
                    'uploadForm'    => $varFormView
                ];
            }
        }

        return [
            'asset'       => $productAsset,
            'locales'     => $locales,
            'attachments' => $attachments
        ];
    }

    /**
     * @param Request    $request
     * @param int|string $assetId
     * @param int|string $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function uploadVariationAction(Request $request, $assetId, $id)
    {
        $variation = $this->variationRepository->find($id);

        if ($request->isMethod('POST')) {
            $form = $this->createUploadForm();
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->get('file')->getData();
                if (null !== $clientFile = $data->getFile()) {
                    // TODO: Generate Metadata
                    $uploadedFile = $this->rawFileStorer->store($clientFile, ProductAssetFileSystems::FS_STORAGE);
                    $variation->setFile($uploadedFile);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($variation);
                    $em->flush();
                }
            }
        }

        return $this->redirect($this->generateUrl('pimee_product_asset_edit', ['id' => $assetId]));
    }

    /**
     * Find a ProductAsset by its id or return a 404 response
     *
     * @param int|string $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface
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
     * @return \Symfony\Component\Form\Form
     */
    protected function createUploadForm()
    {
        return $this->createForm(new UploadType());
    }
}
