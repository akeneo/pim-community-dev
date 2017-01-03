<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;

/**
 * Channel deleted query generator
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelDeletedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        $attributes = $this->namingUtility->getScopableAttributes(false);
        $queries = [];

        foreach ($attributes as $attribute) {
            $attributeNormFields = [
                sprintf(
                    ProductQueryUtility::NORMALIZED_FIELD . '.%s',
                    $attribute->getCode()
                )
            ];

            $localeCodes = $this->namingUtility->getLocaleCodes();
            $attributeNormFields = $this->namingUtility->appendSuffixes($attributeNormFields, $localeCodes);
            $attributeNormFields = $this->namingUtility->appendSuffixes(
                $attributeNormFields,
                [$entity->getCode()]
            );

            foreach ($attributeNormFields as $attributeNormField) {
                $queries[] = [
                    [
                        sprintf('%s', $attributeNormField) => ['$exists' => true]
                    ],
                    [
                        '$unset' => [$attributeNormField => '']
                    ],
                    [
                        'multiple' => true
                    ]
                ];
            }
        }

        return $queries;
    }
}
