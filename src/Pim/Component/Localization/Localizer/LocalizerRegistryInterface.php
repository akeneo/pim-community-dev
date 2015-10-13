<?php

namespace Pim\Component\Localization\Localizer;

/**
 * Register localizers interface
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface LocalizerRegistryInterface
{
    /**
     * Get localizer
     *
     * @param string $attributeType
     *
     * @return LocalizerInterface|null
     */
    public function getLocalizer($attributeType);

    /**
     * Add a localizer
     *
     * @param LocalizerInterface $localizer
     */
    public function addLocalizer(LocalizerInterface $localizer);
}
