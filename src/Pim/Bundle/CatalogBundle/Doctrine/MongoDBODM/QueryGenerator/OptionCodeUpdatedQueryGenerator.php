<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* Option code updated query generator
*/
class OptionCodeUpdatedQueryGenerator extends AbstractQueryGenerator
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
                [$attributeNormField => [ '$exists' => true ]],
                ['$set' => [sprintf('%s.code', $attributeNormField) => $newValue]],
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
            $entity->getAttribute()->getAttributeType() === 'pim_catalog_simpleselect';
    }
}
