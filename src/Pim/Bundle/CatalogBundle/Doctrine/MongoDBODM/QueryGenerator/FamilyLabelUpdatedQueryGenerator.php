<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use Pim\Component\Catalog\Model\FamilyTranslationInterface;

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
     * Generates a query to update all the products belonging to the updated family.
     *
     * @param FamilyTranslationInterface $entity
     *
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        return [[
            ['family' => $entity->getForeignKey()->getId()],
            [
                '$set' => [
                    sprintf('normalizedData.family.labels.%s', $entity->getLocale()) => (string) $newValue
                ]
            ],
            ['multiple' => true]
        ]];
    }
}
