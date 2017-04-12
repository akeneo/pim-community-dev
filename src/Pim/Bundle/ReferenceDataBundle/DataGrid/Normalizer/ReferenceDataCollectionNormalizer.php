<?php

namespace Pim\Bundle\ReferenceDataBundle\DataGrid\Normalizer;

use Pim\Component\ReferenceData\Model\ReferenceDataInterface;
use Pim\Component\ReferenceData\ProductValue\ReferenceDataCollectionProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataCollectionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($referenceDataCollection, $format = null, array $context = [])
    {
        $labels = [];
        foreach ($referenceDataCollection->getData() as $referenceData) {
            $labels[] = $this->getLabel($referenceData);
        }

        sort($labels);

        return [
            'locale' => $referenceDataCollection->getLocale(),
            'scope'  => $referenceDataCollection->getScope(),
            'data'   => implode(', ', $labels),
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof ReferenceDataCollectionProductValueInterface;
    }

    /**
     * Get the reference data label (or the [code] is no label is present).
     *
     * @param ReferenceDataInterface $referenceData
     *
     * @return string
     */
    protected function getLabel(ReferenceDataInterface $referenceData)
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
