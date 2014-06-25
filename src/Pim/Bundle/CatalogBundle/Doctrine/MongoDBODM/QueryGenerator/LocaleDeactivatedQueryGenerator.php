<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

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
        if (!$newValue) {
            $attributes = $this->getLocalizableAttributes();
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