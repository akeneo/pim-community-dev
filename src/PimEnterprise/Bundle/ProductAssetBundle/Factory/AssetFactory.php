<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Factory;

use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Asset factory
 *TODO: component
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AssetFactory
{
    /** @var string */
    protected $assetClass;

    /** @var ReferenceFactory */
    protected $referenceFactory;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;


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
        $this->localeRepository = $localeRepository;
        $this->referenceFactory = $referenceFactory;
        $this->assetClass       = $assetClass;
    }

    /**
     * Create a new Asset with its Reference and Variation
     *
     * @param bool $isLocalized This parameter is used to know how to create Reference
     *
     * @return AssetInterface
     */
    public function create($isLocalized = false)
    {
        $asset = new $this->assetClass();
        if ($isLocalized) {
            foreach ($this->localeRepository->getActivatedLocales() as $locale) {
                $reference = $this->referenceFactory->create($locale);
                $reference->setAsset($asset);
            }
        } else {
            $reference = $this->referenceFactory->create();
            $reference->setAsset($asset);
        }

        return $asset;
    }
}
