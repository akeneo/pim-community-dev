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

use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;

/**
 * Builds references related to an asset
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ReferenceBuilder implements ReferenceBuilderInterface
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
        $referenceClass = 'PimEnterprise\Component\ProductAsset\Model\Reference'
    ) {
        $this->localeRepository = $localeRepository;
        $this->referenceClass   = $referenceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildAllLocalized(AssetInterface $asset)
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
    public function buildMissingLocalized(AssetInterface $asset)
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
    public function buildOne(AssetInterface $asset, LocaleInterface $locale = null)
    {
        $reference = new $this->referenceClass();
        $reference->setAsset($asset);

        if (null !== $locale) {
            $reference->setLocale($locale);
        }

        return $reference;
    }
}
