<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
 * Attribute deleted query generator
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeDeletedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($attribute, $field, $oldValue, $newValue)
    {
        $queries = [];
        $attributeNormFields = $this->namingUtility->getAttributeNormFields($attribute);

        foreach ($attributeNormFields as $attributeNormField) {
            $queries[] = [
                [$attributeNormField => [ '$exists'          => true ]],
                ['$unset'            => [$attributeNormField => '']],
                [
                    'multiple' => true,
                    'w' => 0, // not acknowledged
                ]
            ];
        }

        return $queries;
    }
}
