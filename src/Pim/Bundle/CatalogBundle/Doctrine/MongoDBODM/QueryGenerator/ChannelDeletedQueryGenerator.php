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
        $attributes = $this->attributeNamingUtility->getScopableAttributes(false);
        $queries = [];

        foreach ($attributes as $attribute) {

            $attributeNormFields = [
                sprintf(
                    ProductQueryUtility::NORMALIZED_FIELD . '.%s',
                    $attribute->getCode()
                )
            ];

            $localeCodes = $this->attributeNamingUtility->getLocaleCodes();
            $attributeNormFields = $this->attributeNamingUtility->appendSuffixes($attributeNormFields, $localeCodes);
            $attributeNormFields = $this->attributeNamingUtility->appendSuffixes(
                $attributeNormFields,
                [$entity->getCode()]
            );

            foreach ($attributeNormFields as $attributeNormField) {
                $queries[] = [
                    [sprintf('%s', $attributeNormField) => [ '$exists' => true ]],
                    ['$unset' => [$attributeNormField => '']],
                    ['multiple' => true]
                ];
            }
        }

        return $queries;
    }
}