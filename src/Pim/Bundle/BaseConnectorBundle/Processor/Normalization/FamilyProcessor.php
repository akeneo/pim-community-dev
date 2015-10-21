<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Normalization;

use Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Processes and transforms families object to array of normalized families
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyProcessor extends Processor
{
    /** @var NormalizerInterface */
    protected $familyNormalizer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param SerializerInterface       $serializer
     * @param NormalizerInterface       $familyNormalizer
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        SerializerInterface $serializer,
        NormalizerInterface $familyNormalizer,
        LocaleRepositoryInterface $localeRepository
    ) {
        parent::__construct($serializer, $localeRepository);

        $this->familyNormalizer = $familyNormalizer;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($family)
    {
        $normalizedFamily = $this->familyNormalizer->normalize($family);

        return $this->serializer->serialize(
            $normalizedFamily,
            'csv',
            [
                'delimiter'     => $this->delimiter,
                'enclosure'     => $this->enclosure,
                'withHeader'    => $this->withHeader,
                'heterogeneous' => false,
                'locales'       => $this->localeRepository->getActivatedLocaleCodes(),
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
