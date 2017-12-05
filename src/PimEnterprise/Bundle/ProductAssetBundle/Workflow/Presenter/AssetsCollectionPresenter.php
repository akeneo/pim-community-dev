<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Workflow\Presenter;

use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Present changes on a collection of assets.
 *
 * TODO: ideally, the type of attribute supported by Presenters should be injected in the DI
 * TODO: so that we don't have to create this useless class. Also that would avoid to couple
 * TODO: ProductAssetBundle to WorkflowBundle...
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class AssetsCollectionPresenter implements PresenterInterface
{
    /** @var AssetRepositoryInterface */
    protected $repository;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param AssetRepositoryInterface $repository
     * @param RouterInterface          $router
     */
    public function __construct(
        AssetRepositoryInterface $repository,
        RouterInterface $router
    ) {
        $this->repository = $repository;
        $this->router     = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function present($data, array $change)
    {
        $beforeCodes = array_map(function (AssetInterface $asset) {
            return $asset->getCode();
        }, $data->getData());
        $afterCodes = $change['data'];

        return [
            'before' => $this->presentAssets($beforeCodes),
            'after'  => $this->presentAssets($afterCodes)
        ];
    }



    /**
     * {@inheritdoc}
     */
    public function presentAssets($assetCodes)
    {
        if (null === $assetCodes) {
            return null;
        }

        $result = '';
        $assets = $this->repository->findBy(['code' => $assetCodes]);

        foreach ($assets as $asset) {
            $variation = $asset->getVariations()[0];
            $result .= sprintf(
                '<div class="AknThumbnail" style="background-image: url(\'%s\')">' .
                    '<span class="AknThumbnail-label">%s</span>' .
                '</div>',
                $this->router->generate('pimee_product_asset_thumbnail', [
                    'code'        => $asset->getCode(),
                    'filter'      => 'thumbnail',
                    'channelCode' => $variation->getChannel()->getCode(),
                    'localeCode'  => null !== $variation->getLocale() ? $variation->getLocale()->getCode() : null
                ]),
                substr($asset->getDescription(), 0, 30) . (strlen($asset->getDescription()) > 30 ? '...' : '')
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($value)
    {
        return AttributeTypes::ASSETS_COLLECTION === $value->getAttribute()->getType();
    }
}
