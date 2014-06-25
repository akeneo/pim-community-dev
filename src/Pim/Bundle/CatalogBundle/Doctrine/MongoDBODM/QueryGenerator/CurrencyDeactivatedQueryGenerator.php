<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;

/**
* Currency deactivated query generator
*/
class CurrencyDeactivatedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        if (!$newValue) {
            $attributes = $this->attributeNamingUtility->getPricesAttributes(false);

            $queries = [];

            foreach ($attributes as $attribute) {
                $attributeNormFields = $this->getPossibleAttributeCodes(
                    $attribute,
                    ProductQueryUtility::NORMALIZED_FIELD
                );

                foreach ($attributeNormFields as $attributeNormField) {
                    $queries[] = [
                        [sprintf('%s', $attributeNormField, $entity->getCode()) => [ '$exists' => true ]],
                        ['$unset' => [sprintf('%s.%s', $attributeNormField, $entity->getCode()) => '']],
                        ['multi' => true]
                    ];
                }
            }

            return $queries;
        } else {
            return [];
        }
    }
}