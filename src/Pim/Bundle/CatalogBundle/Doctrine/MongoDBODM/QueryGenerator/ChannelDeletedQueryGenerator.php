<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* Channel deleted query generator
*/
class ChannelDeletedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        $attributes = $this->getScopableAttributes();
        $queries = [];

        foreach ($attributes as $attribute) {
            $attributeCodes = $this->getPossibleAttributeCodes($attribute, 'normalizedData.');

            foreach ($attributeCodes as $attributeCode) {
                $queries[] = [
                    [sprintf('%s', $attributeCode) => [ '$exists' => true ]],
                    ['$unset' => [$attributeCode => '']],
                    ['multi' => true]
                ];

            }
        }

        return $queries;
    }
}