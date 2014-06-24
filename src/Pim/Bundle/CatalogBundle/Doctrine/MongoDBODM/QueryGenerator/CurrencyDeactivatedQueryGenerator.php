<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

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
            $attributeManager = $this->registry->getManagerForClass($this->attributeClass);
            $attributeRepository = $attributeManager->getRepository($this->attributeClass);

            $attributes = $attributeRepository->findBy(
                [
                    'attributeType' => 'pim_catalog_price_collection'
                ]
            );

            $queries = [];

            foreach ($attributes as $attribute) {
                $attributeNormFields = $this->getPossibleAttributeCodes($attribute, 'normalizedData.');

                foreach ($attributeNormFields as $attributeNormField) {
                    $queries[] = [
                        [sprintf(
                            '%s',
                            $attributeNormField,
                            $entity->getCode()
                        ) => [ '$exists' => true ]],
                        ['$unset' => [sprintf(
                            '%s.%s',
                            $attributeNormField,
                            $entity->getCode()
                        ) => '']],
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