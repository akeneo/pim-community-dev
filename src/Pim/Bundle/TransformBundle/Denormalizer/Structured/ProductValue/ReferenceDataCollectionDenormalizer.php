<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionDenormalizer extends AbstractValueDenormalizer
{
    /** @var DenormalizerInterface */
    protected $referenceDataDenormalizer;

    /**
     * @param array                 $supportedTypes
     * @param DenormalizerInterface $referenceDataDenormalizer
     */
    public function __construct(
        array $supportedTypes,
        DenormalizerInterface $referenceDataDenormalizer
    ) {
        parent::__construct($supportedTypes);

        $this->referenceDataDenormalizer = $referenceDataDenormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $referenceDataClass, $format = null, array $context = array())
    {
        if (empty($data)) {
            return new ArrayCollection();
        }

        if (false === is_array($data)) {
            throw new InvalidParameterException(sprintf('Data expected to be an array.'));
        }

        $referenceDataColl = new ArrayCollection();

        foreach ($data as $singleData) {
            $referenceData = $this->referenceDataDenormalizer
                ->denormalize($singleData, $referenceDataClass, $format, $context);

            if (null !== $referenceData) {
                $referenceDataColl->add($referenceData);
            }
        }

        return $referenceDataColl;
    }
}
