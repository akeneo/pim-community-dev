<?php

namespace Pim\Component\Localization\Localizer;

/**
 * Check and convert if number provided respects the format expected
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberLocalizer extends AbstractNumberLocalizer
{
    /**
     * {@inheritdoc}
     */
    public function isValid($number, array $options = [], $attributeCode)
    {
        return $this->isValidNumber($number, $options, $attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function convertLocalizedToDefault($number, array $options = [])
    {
        return $this->convertNumber($number, $options);
    }
}
