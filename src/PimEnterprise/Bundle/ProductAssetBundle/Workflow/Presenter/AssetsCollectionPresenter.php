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

use PimEnterprise\Bundle\WorkflowBundle\Presenter\AbstractProductValuePresenter;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Present changes on a collection of assets.
 *
 * TODO: ideally, the type of attribute supported by Presenters should be injected in the DI
 * TODO: so that we don't have to create this useless class. Also that would avoid to couple
 * TODO: ProductAssetBundle to WorkflowBundle...
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class AssetsCollectionPresenter extends AbstractProductValuePresenter
{
    /** @var AssetRepositoryInterface */
    protected $repository;

    /**
     * @param AssetRepositoryInterface $repository
     */
    public function __construct(AssetRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return 'pim_assets_collection' === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        $result = [];

        if (null === $data) {
            return $result;
        }

        foreach ($data as $reference) {
            $result[] = (string) $reference;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        if (null === $change['data']) {
            return null;
        }

        $result = [];
        $assets = $this->repository->findBy(['code' => $change['data']]);

        foreach ($assets as $asset) {
            $result[] = (string) $asset;
        }

        return $result;
    }
}
