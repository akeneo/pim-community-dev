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
        if ($newValue !== true) {
            $attributes = $this->attributeNamingUtility->getPricesAttributes(false);

            $queries = [];

            foreach ($attributes as $attribute) {
                $attributeNormFields = $this->attributeNamingUtility->getAttributeNormFields($attribute);

                foreach ($attributeNormFields as $attributeNormField) {
                    $queries[] = [
                        [sprintf('%s.%s', $attributeNormField, $entity->getCode()) => [ '$exists' => true ]],
                        ['$unset' => [sprintf('%s.%s', $attributeNormField, $entity->getCode()) => '']],
                        ['multiple' => true]
                    ];
                }
            }

            return $queries;
        } else {
            return [];
        }
    }
}