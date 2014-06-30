<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* AttributeAsLabel updated query generator
*/
class AttributeAsLabelUpdatedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        return [[
            ['family' => $entity->getId()],
            ['$set' => ['normalizedData.family.attributeAsLabel' => (string) $newValue]],
            ['multiple' => true]
        ]];
    }
}
