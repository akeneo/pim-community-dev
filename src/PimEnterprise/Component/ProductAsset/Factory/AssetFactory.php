<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Factory;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Asset factory
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetFactory implements SimpleFactoryInterface
{
    /** @var ReferenceFactory */
    protected $referenceFactory;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var string */
    protected $assetClass;

    /**
     * @param ReferenceFactory          $referenceFactory
     * @param LocaleRepositoryInterface $localeRepository
     * @param string                    $assetClass
     */
    public function __construct(
        ReferenceFactory $referenceFactory,
        LocaleRepositoryInterface $localeRepository,
        $assetClass
    ) {
        $this->referenceFactory = $referenceFactory;
        $this->localeRepository = $localeRepository;
        $this->assetClass       = $assetClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->assetClass();
    }

    /**
     * Create references for an asset.
     *
     * @param AssetInterface $asset
     * @param bool           $isLocalized
     */
    public function createReferences(AssetInterface $asset, $isLocalized)
    {
        if (null === $asset->getId()) {
            if (true === $isLocalized) {
                foreach ($this->localeRepository->getActivatedLocales() as $locale) {
                    $reference = $this->referenceFactory->create($locale);
                    $reference->setAsset($asset);
                }
            } else {
                $reference = $this->referenceFactory->create();
                $reference->setAsset($asset);
            }
        }
    }
}
