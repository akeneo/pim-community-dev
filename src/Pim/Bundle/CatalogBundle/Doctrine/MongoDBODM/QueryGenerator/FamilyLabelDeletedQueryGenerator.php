<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
 * Family label deleted query generator
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyLabelDeletedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        return [[
            ['family' => $entity->getForeignKey()->getId()],
            [
                '$unset' => [
                    sprintf('normalizedData.family.labels.%s', $entity->getLocale()) => ''
                ]
            ],
            ['multiple' => true]
        ]];
    }
}
