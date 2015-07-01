<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
     *
     * @return RemoverRegistryInterface
     */
    public function register(RemoverInterface $remover);

    /**
     * Get the field remover
     *
     * @param string $field the field
     *
     * @return FieldRemoverInterface|null
     */
    public function getFieldRemover($field);

    /**
     * Get the attribute remover
     *
     * @param AttributeInterface $attribute
     *
     * @return AttributeRemoverInterface|null
     */
    public function getAttributeRemover(AttributeInterface $attribute);
}
