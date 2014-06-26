<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* Family label updated query generator
*/
class FamilyLabelUpdatedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        return [[
            ['family' => $entity->getId()],
            [
                '$set' => [
                    sprintf('normalizedData.family.label.%s', $entity->getLocale()) => (string) $newValue
                ]
            ],
            ['multiple' => true]
        ]];
    }
}