<?php

namespace Pim\Bundle\ReferenceDataBundle\DataGrid\Extension\Formatter\Property;

use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\FieldProperty;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Able to render a reference data type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataProperty extends FieldProperty
{
    /** @var ConfigurationRegistryInterface */
    protected $referenceDataRegistry;

    /**
     * @param TranslatorInterface            $translator
     * @param ConfigurationRegistryInterface $registry
     */
    public function __construct(TranslatorInterface $translator, ConfigurationRegistryInterface $registry)
    {
        $this->translator = $translator;
        $this->referenceDataRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $referenceDataType = $value['attribute']['properties']['reference_data_name'];
        $referenceData = $value[$referenceDataType];

        if (isset($referenceData['code'])) {
            return $this->getReferenceDataLabel($referenceData, $referenceDataType);
        }

        if (is_array($referenceData)) {
            $labels = [];
            foreach ($referenceData as $data) {
                $labels[] = $this->getReferenceDataLabel($data, $referenceDataType);
            }

            return implode(', ', $labels);
        }

        return null;
    }

    /**
     * Get the reference data label (or the [code] is no label is present).
     *
     * @param array  $referenceData
     * @param string $referenceDataType
     *
     * @return string
     */
    protected function getReferenceDataLabel(array $referenceData, $referenceDataType)
    {
        $referenceDataClass = $this->getReferenceDataClass($referenceDataType);
        $labelProperty = $referenceDataClass::getLabelProperty();

        if (null !== $labelProperty && isset($referenceData[$labelProperty])) {
            $label = $referenceData[$labelProperty];

            if (!empty($label)) {
                return $label;
            }
        }

        return sprintf('[%s]', $referenceData['code']);
    }

    /**
     * Get the class of a reference data type
     *
     * @param string $referenceDataType
     *
     * @return string
     */
    protected function getReferenceDataClass($referenceDataType)
    {
        $configuration = $this->referenceDataRegistry->get($referenceDataType);

        return $configuration->getClass();
    }
}
