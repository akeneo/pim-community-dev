<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* Option deleted query generator
*/
class MultipleOptionCodeUpdatedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        $attributeNormFields = $this->attributeNamingUtility->getAttributeNormFields($entity->getAttribute());

        $queries = [];

        foreach ($attributeNormFields as $attributeNormField) {
            $queries[] = [
                [$attributeNormField => [ '$elemMatch' => ['code' => $oldValue] ]],
                ['$set' => [$attributeNormField . '.$.code' => $newValue]],
                ['multiple' => true]
            ];
        }

        return $queries;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity, $field)
    {
        return parent::supports($entity, $field) &&
            $entity->getAttribute()->getAttributeType() === 'pim_catalog_multiselect';
    }
}
