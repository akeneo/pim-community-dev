<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Registry of copiers
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CopierRegistryInterface
{
    /**
     * Register a copier
     *
     * @param CopierInterface $copier
     */
    public function register(CopierInterface $copier): \Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface;

    /**
     * Get a copier compatible with the given properties
     *
     * @param string $fromProperty
     * @param string $toProperty
     */
    public function getCopier(string $fromProperty, string $toProperty): \Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierInterface;

    /**
     * @param string $fromField
     * @param string $toField
     */
    public function getFieldCopier(string $fromField, string $toField): FieldCopierInterface;

    /**
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     */
    public function getAttributeCopier(AttributeInterface $fromAttribute, AttributeInterface $toAttribute): AttributeCopierInterface;
}
