<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Normalization;

use Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Processes and transforms families object to array of normalized families
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyProcessor extends Processor
{
    /** @var NormalizerInterface */
    protected $familyNormalizer;

    /**
     * @param SerializerInterface $serializer
     * @param LocaleManager       $localeManager
     * @param NormalizerInterface $familyNormalizer
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleManager $localeManager,
        NormalizerInterface $familyNormalizer
    ) {
        parent::__construct($serializer, $localeManager);

        $this->familyNormalizer = $familyNormalizer;
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
