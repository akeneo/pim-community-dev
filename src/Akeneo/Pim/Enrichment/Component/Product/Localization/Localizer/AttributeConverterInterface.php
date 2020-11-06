<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Convert localized attributes
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeConverterInterface
{
    /**
     * Convert localized attributes to default format
     *
     * @param array $items
     * @param array $options
     */
    public function convertToDefaultFormats(array $items, array $options = []): array;

    /**
     * Convert localized attributes to localized format
     *
     * @param array $items
     * @param array $options
     */
    public function convertToLocalizedFormats(array $items, array $options = []): array;

    /**
     * Get list of violations return by localizers
     */
    public function getViolations(): ConstraintViolationListInterface;
}
