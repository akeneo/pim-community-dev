<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Object id resolver interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ObjectIdResolverInterface
{
    /**
     * Get ids for the given codes
     *
     * @param string                  $field
     * @param array                   $codes
     * @param AttributeInterface|null $attribute
     *
     * @return int[]
     */
    public function getIdsFromCodes($field, array $codes, AttributeInterface $attribute = null);

    /**
     * Add a mapping to the field mapping
     *
     * @param string $field
     * @param string $className
     */
    public function addFieldMapping($field, $className);
}
