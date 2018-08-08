<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Association;

/**
 * Filter associations to remove existing parent associations
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParentAssociationsFilter
{
    /**
     * Filter associations to remove associations existing in parents
     *
     * @param array $associations
     * @param array $parentAssociations
     *
     * @return array $associations not existing in the ancestors
     */
    public function filterParentAssociations(array $associations, array $parentAssociations): array
    {
        $filtered = [];
        foreach ($associations as $associationTypeCode => $associationTypeValues) {
            $filtered[$associationTypeCode] = $associationTypeValues;
            if (isset($parentAssociations[$associationTypeCode])) {
                $filtered[$associationTypeCode] = $this->filterParentAssociationType(
                    $associationTypeValues, $parentAssociations[$associationTypeCode]
                );
            }
        }

        return $filtered;
    }

    /**
     * Filter associations in a given association type
     *
     * @param array $associationType
     * @param array $parentAssociationType
     *
     * @return array
     */
    protected function filterParentAssociationType(array $associationType, array $parentAssociationType): array
    {
        $filteredType = $associationType;

        foreach ($associationType as $property => $value) {
            if (isset($parentAssociationType[$property])) {
                // using of array_values will reset the keys
                $filteredType[$property] = array_values(array_diff($value, $parentAssociationType[$property]));
            }
        }

        return $filteredType;
    }
}
