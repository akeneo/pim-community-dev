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
        $attributeNormFields = $this->getPossibleAttributeCodes(
            $entity->getAttribute(),
            'normalizedData.'
        );

        $queries = [];

        foreach ($attributeNormFields as $attributeNormField) {
            $queries[] = [
                [$attributeNormField => [ '$exists' => true ]],
                [
                    '$set' => [
                        sprintf('%s.code', $attributeNormField) => $newValue
                    ]
                ],
                ['multi' => true]
            ];
        }

        return $queries;
    }
}