<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Normalization;

use Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Processes and transforms families object to array of normalized families
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated Will be removed in 1.6, please use
 *             Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\HomogeneousProcessor
 */
class FamilyProcessor extends Processor
{
    /** @var NormalizerInterface */
    protected $familyNormalizer;

    /**
     * @param SerializerInterface       $serializer
     * @param LocaleRepositoryInterface $localeRepository
     * @param NormalizerInterface       $familyNormalizer Not used anymore, we keep it to avoid BC break
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $familyNormalizer
    ) {
        parent::__construct($serializer, $localeRepository);

        $this->familyNormalizer = $familyNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($family)
    {
        $normalizedFamily = $this->familyNormalizer->normalize($family);
        $parameters = $this->stepExecution->getJobParameters();

        return $this->serializer->serialize(
            $normalizedFamily,
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
