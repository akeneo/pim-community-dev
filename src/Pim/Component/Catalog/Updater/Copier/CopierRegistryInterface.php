<?php

namespace Pim\Component\Catalog\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
     *
     * @return CopierRegistryInterface
     */
    public function register(CopierInterface $copier);

    /**
     * @param string $fromField
     * @param string $toField
     *
     * @return FieldCopierInterface
     */
    public function getFieldCopier($fromField, $toField);

    /**
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     *
     * @return AttributeCopierInterface
     */
    public function getAttributeCopier(AttributeInterface $fromAttribute, AttributeInterface $toAttribute);
}
