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
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\EnrichBundle\Form\Type\UploadType;
use PimEnterprise\Bundle\ProductAssetBundle\Command\GenerateVariationFileCommand;
use PimEnterprise\Component\ProductAsset\FileStorage\ProductAssetFileSystems;
use PimEnterprise\Component\ProductAsset\FileStorage\RawFile\RawFileStorerInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetVariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\FileMetadataRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ProductAssetReferenceRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ProductAssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ProductAssetVariationRepositoryInterface;
use PimEnterprise\Component\ProductAsset\VariationFileGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\PhpExecutableFinder;

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

    /** @var ProductAssetReferenceRepositoryInterface */
    protected $referenceRepository;

    /** @var ProductAssetVariationRepositoryInterface */
    protected $variationRepository;

    /** @var RawFileStorerInterface */
    protected $rawFileStorer;

    /** @var VariationFileGeneratorInterface */
    protected $variationFileGenerator;

    /**
     * @param ProductAssetRepositoryInterface          $assetRepository
     * @param ProductAssetReferenceRepositoryInterface $referenceRepository
     * @param ProductAssetVariationRepositoryInterface $variationRepository
     * @param FileMetadataRepositoryInterface          $metadataRepository
     * @param ChannelRepositoryInterface               $channelRepository
     * @param RawFileStorerInterface                   $rawFileStorer
     * @param VariationFileGeneratorInterface          $variationFileGenerator
     */
    public function __construct(
        ProductAssetRepositoryInterface $assetRepository,
        ProductAssetReferenceRepositoryInterface $referenceRepository,
        ProductAssetVariationRepositoryInterface $variationRepository,
        FileMetadataRepositoryInterface $metadataRepository,
        ChannelRepositoryInterface $channelRepository,
        RawFileStorerInterface $rawFileStorer,
        VariationFileGeneratorInterface $variationFileGenerator
    ) {
        $this->assetRepository        = $assetRepository;
        $this->referenceRepository    = $referenceRepository;
        $this->variationRepository    = $variationRepository;
        $this->metadataRepository     = $metadataRepository;
        $this->channelRepository      = $channelRepository;
        $this->rawFileStorer          = $rawFileStorer;
        $this->variationFileGenerator = $variationFileGenerator;
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
     * TODO: AclAncestor("pimee_product_asset_index")
     *
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $productAsset = $this->findProductAssetOr404($id);
        $channels     = $this->channelRepository->getFullChannels();
        $references   = $productAsset->getReferences();

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
                    $varFormView                                                               = $this->createUploadForm(
                    )->createView();
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

        return [
            'asset'       => $productAsset,
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
        /** @var ProductAssetVariationInterface $variation */
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

    /**
     * @param ProductAssetInterface $asset
     * @param ChannelInterface      $channel
     * @param LocaleInterface       $locale
     *
     * @throws \Exception
     */
    protected function launchVariationFileGeneration(
        ProductAssetInterface $asset,
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
