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
use PimEnterprise\Component\ProductAsset\Repository\FileMetadataRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Repository\ProductAssetRepositoryInterface;
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

    /**
     * @param ProductAssetRepositoryInterface $assetRepository
     * @param FileMetadataRepositoryInterface $metadataRepository
     */
    public function __construct(
        ProductAssetRepositoryInterface $assetRepository,
        FileMetadataRepositoryInterface $metadataRepository
    ) {
        $this->assetRepository    = $assetRepository;
        $this->metadataRepository = $metadataRepository;
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
}
