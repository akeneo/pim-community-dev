<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
 * Family deleted query generator
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyDeletedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        return [
            [
                ['normalizedData.family.code' => $entity->getCode()],
                ['$unset'                     => ['normalizedData.family' => '']],
                ['multiple'                   => true]
            ]
        ];
    }
}
