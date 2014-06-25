<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;

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
            $attributeCodes = $this->getPossibleAttributeCodes($attribute, ProductQueryUtility::NORMALIZED_FIELD);

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