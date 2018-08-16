<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;

/**
 * Register localizers interface. This interface manage two sets of localizers:
 * - the localizers for all the localizable attributes,
 * - the localizers for the ProductValue attributes.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocalizerRegistryInterface
{
    /**
     * Register a localizer
     *
     * @param LocalizerInterface $localizer
     */
    public function register(LocalizerInterface $localizer);

    /**
     * Get the localizer supported by a product value
     *
     * @param string $attributeType
     *
     * @return LocalizerInterface|null
     */
    public function getLocalizer($attributeType);
}
