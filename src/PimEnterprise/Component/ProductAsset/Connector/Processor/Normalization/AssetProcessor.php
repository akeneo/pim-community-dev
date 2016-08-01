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

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Processes and transforms assets to array of assets
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 *
 * @deprecated will be removed in 1.7 use @pim_connector.processor.normalization.class
 */
class AssetProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

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
        $this->serializer = $serializer;
        $this->localeRepository = $localeRepository;
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

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
