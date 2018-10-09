<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Asset\Component\Builder;

use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;

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
        $referenceClass = 'Akeneo\Asset\Component\Model\Reference'
    ) {
        $this->localeRepository = $localeRepository;
        $this->referenceClass = $referenceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildAllLocalized(AssetInterface $asset)
    {
        $references = [];
        $locales = $this->localeRepository->getActivatedLocales();

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
        $locales = $this->localeRepository->getActivatedLocales();

        foreach ($locales as $locale) {
            if (!$asset->isEmpty($locale)) {
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
