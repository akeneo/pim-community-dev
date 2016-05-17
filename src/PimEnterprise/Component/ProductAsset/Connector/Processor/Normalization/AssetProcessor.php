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
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Processes and transforms assets to array of assets
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 *
 * @deprecated will be removed in 1.7 use @pim_connector.processor.normalization.simple.class
 */
class AssetProcessor extends Processor
{
    /** @var NormalizerInterface */
    protected $assetNormalizer;

    /**
     * @param SerializerInterface       $serializer
     * @param LocaleRepositoryInterface $localeRepository
     * @param NormalizerInterface       $assetNormalizer
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $assetNormalizer
    ) {
        parent::__construct($serializer, $localeRepository);

        $this->assetNormalizer = $assetNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($asset)
    {
        $normalizedAsset = $this->assetNormalizer->normalize($asset);
        $parameters = $this->stepExecution->getJobParameters();
        return $this->serializer->serialize(
            $normalizedAsset,
            'csv',
            [
                'delimiter'     => $parameters->get('delimiter'),
                'enclosure'     => $parameters->get('enclosure'),
                'withHeader'    => $parameters->get('withHeader'),
                'heterogeneous' => false,
                'locales'       => $this->localeRepository->getActivatedLocaleCodes(),
            ]
        );
    }
}
