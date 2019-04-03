<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Remove a value from an entity with values
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeRemoverInterface extends RemoverInterface
{
    /**
     * Remove attribute data
     *
     * @param EntityWithValuesInterface $entityWithValues The entity to modify
     * @param AttributeInterface        $attribute        The attribute of the entity to modify
     * @param mixed                     $data             The data to remove
     * @param array                     $options          Options passed to the remover
     *
     * @return void
     */
    public function removeAttributeData(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ): void;

    /**
     * Supports the attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute);
}
