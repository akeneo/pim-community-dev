<?php

namespace Pim\Bundle\ReferenceDataBundle\DataGrid\Normalizer;

use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\ProductValue\ReferenceDataProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($referenceData, $format = null, array $context = [])
    {
        return [
            'locale' => $referenceData->getLocale(),
            'scope'  => $referenceData->getScope(),
            'data'   => $this->getReferenceDataLabel($referenceData->getData()),
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof ReferenceDataProductValueInterface;
    }

    /**
     * Get the reference data label (or the [code] is no label is present).
     *
     * @param ReferenceDataInterface $referenceData
     *
     * @return string
     */
    protected function getReferenceDataLabel(ReferenceDataInterface $referenceData)
    {
        if (null !== $labelProperty = $referenceData::getLabelProperty()) {
            $getter = 'get' . ucfirst($labelProperty);
            $label = $referenceData->$getter();

            if (!empty($label)) {
                return $label;
            }
        }

        return sprintf('[%s]', $referenceData->getCode());
    }
}
