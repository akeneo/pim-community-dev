<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* Family label updated query generator
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyLabelUpdatedQueryGenerator extends AbstractQueryGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        return [[
            ['family' => $entity->getId()],
            [
                '$set' => [
                    sprintf('normalizedData.family.label.%s', $entity->getLocale()) => (string) $newValue
                ]
            ],
            ['multiple' => true]
        ]];
    }
}
