<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* Option value updated query generator
*/
class OptionValueUpdatedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        $attributeNormFields = $this->attributeNamingUtility->getAttributeNormFields(
            $entity->getOption()->getAttribute()
        );

        $queries = [];

        foreach ($attributeNormFields as $attributeNormField) {
            $queries[] = [
                [$attributeNormField . '.code' => $entity->getOption()->getCode()],
                [
                    '$set' => [
                        sprintf(
                            '%s.optionValues.%s.value',
                            $attributeNormField,
                            $entity->getLocale()
                        ) => $newValue
                    ]
                ],
                ['multiple' => true]
            ];
        }

        return $queries;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity, $field)
    {
        return parent::supports($entity, $field) &&
            $entity->getOption()->getAttribute()->getAttributeType() === 'pim_catalog_simpleselect';
    }
}
