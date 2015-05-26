<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Builder;

use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\ProductAssetInterface;

/**
 * Builds references related to an asset
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductAssetReferenceBuilder implements ProductAssetReferenceBuilderInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var string */
    protected $referenceClass;

    /**
     * @param LocaleRepositoryInterface $localeRepository
     * @param string                    $referenceClass
     */
    public function __construct(
        LocaleRepositoryInterface $localeRepository,
        $referenceClass = 'PimEnterprise\Component\ProductAsset\Model\ProductAssetReference'
    ) {
        $this->localeRepository = $localeRepository;
        $this->referenceClass   = $referenceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildAllLocalized(ProductAssetInterface $asset)
    {
        $references = [];
        $locales    = $this->localeRepository->getActivatedLocales();

        foreach ($locales as $locale) {
            $references[] = $this->buildOne($asset, $locale);
        }

        return $references;
    }

    /**
     * {@inheritdoc}
     */
    public function buildMissingLocalized(ProductAssetInterface $asset)
    {
        $references = [];
        $locales    = $this->localeRepository->getActivatedLocales();

        foreach ($locales as $locale) {
            if (!$asset->hasReference($locale)) {
                $references[] = $this->buildOne($asset, $locale);
            }
        }

        return $references;
    }

    /**
     * {@inheritdoc}
     */
    public function buildOne(ProductAssetInterface $asset, LocaleInterface $locale = null)
    {
        $reference = new $this->referenceClass();
        $reference->setAsset($asset);

        if (null !== $locale) {
            $reference->setLocale($locale);
        }

        return $reference;
    }
}
