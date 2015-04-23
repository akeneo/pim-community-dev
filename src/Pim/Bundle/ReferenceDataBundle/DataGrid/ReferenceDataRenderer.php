<?php

namespace Pim\Bundle\ReferenceDataBundle\DataGrid;

use Pim\Component\ReferenceData\MethodNameGuesser;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * Renders a reference data: displays either the label or the [code] of the reference data.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataRenderer
{
    /**
     * @param ReferenceDataInterface $referenceData
     * @param bool                   $fallbackOnCode
     *
     * @return string|null
     */
    public function render(ReferenceDataInterface $referenceData, $fallbackOnCode = true)
    {
        if (null !== $labelProperty = $referenceData::getLabelProperty()) {
            $getter = MethodNameGuesser::guess('get', $labelProperty);
            $label = $referenceData->$getter();

            if (!empty($label)) {
                return $label;
            }
        }

        if ($fallbackOnCode) {
            return sprintf('[%s]', $referenceData->getCode());
        }

        return null;
    }

    /**
     * @param ReferenceDataInterface $referenceData
     *
     * @return string
     */
    public function getLabelProperty(ReferenceDataInterface $referenceData)
    {
        return $labelProperty = $referenceData::getLabelProperty();
    }
}
