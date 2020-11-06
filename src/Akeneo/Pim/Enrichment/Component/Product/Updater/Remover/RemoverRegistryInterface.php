<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Registry of removers
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RemoverRegistryInterface
{
    /**
     * Register a remover
     *
     * @param RemoverInterface $remover
     */
    public function register(RemoverInterface $remover): \Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistryInterface;

    /**
     * Get a remover compatible with the given property
     *
     * @param string $property
     */
    public function getRemover(string $property): \Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverInterface;

    /**
     * Get the field remover
     *
     * @param string $field the field
     */
    public function getFieldRemover(string $field): ?FieldRemoverInterface;

    /**
     * Get the attribute remover
     *
     * @param AttributeInterface $attribute
     */
    public function getAttributeRemover(AttributeInterface $attribute): ?AttributeRemoverInterface;
}
