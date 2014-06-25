<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* Option deleted query generator
*/
class OptionDeletedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        $attributeNormFields = $this->attributeNamingUtility->getPossibleAttributeCodes(
            $entity->getAttribute(),
            ProductQueryUtility::NORMALIZED_FIELD . '.'
        );

        $queries = [];

        foreach ($attributeNormFields as $attributeNormField) {
            $queries[] = [
                [$attributeNormField => [ '$exists' => true ]],
                ['$unset' => [sprintf('%s', $entity->getCode()) => '']],
                ['multi' => true]
            ];
        }

        return $queries;
    }
}