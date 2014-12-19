<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common;

interface ObjectIdResolverInterface
{
    /**
     * Get ids for the given codes
     * @param string $field
     * @param array  $codes
     *
     * @return int[]
     */
    public function getIdsFromCodes($field, $codes);

    /**
     * Add a mapping to the field mapping
     * @param string $field
     * @param string $className
     */
    public function addFieldMapping($field, $className);
}
