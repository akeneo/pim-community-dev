<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Connector\Processor\Normalization;

use Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Processes and transforms assets to array of assets
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AssetProcessor extends Processor
{
    /** @var NormalizerInterface */
    protected $assetNormalizer;

    /**
     * @param SerializerInterface $serializer
     * @param LocaleManager       $localeManager
     * @param NormalizerInterface $assetNormalizer
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleManager $localeManager,
        NormalizerInterface $assetNormalizer
    )
    {
        parent::__construct($serializer, $localeManager);
        $this->assetNormalizer = $assetNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($asset)
    {
        $normalizedAsset = $this->assetNormalizer->normalize($asset);

        return $this->serializer->serialize(
            $normalizedAsset,
            'csv',
            [
                'delimiter'     => $this->delimiter,
                'enclosure'     => $this->enclosure,
                'withHeader'    => $this->withHeader,
                'heterogeneous' => false,
                'locales'       => $this->localeManager->getActiveCodes(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }
}
