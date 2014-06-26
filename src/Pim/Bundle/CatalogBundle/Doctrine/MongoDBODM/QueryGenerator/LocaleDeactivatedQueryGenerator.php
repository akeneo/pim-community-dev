<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;

/**
* Locale deactivated query generator
*/
class LocaleDeactivatedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        if ($newValue !== true) {
            $attributes = $this->attributeNamingUtility->getLocalizableAttributes(false);
            $queries = [];

            foreach ($attributes as $attribute) {
                $attributeNormFields = [
                    sprintf(
                        ProductQueryUtility::NORMALIZED_FIELD . '.%s-%s',
                        $attribute->getCode(),
                        $entity->getCode()
                    )
                ];
                $channelCodes        = $this->attributeNamingUtility->getChannelCodes($attribute);
                $attributeNormFields = $this->attributeNamingUtility->appendSuffixes(
                    $attributeNormFields,
                    $channelCodes
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
        } else {
            return [];
        }
    }
}