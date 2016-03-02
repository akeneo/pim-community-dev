<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
 * NormalizedData query generator interface
 *
 * The interface is meant to generate queries to execute in case of modification on other entities.
 * For example if we remove a channel, we need to remove every product values for this channel in normalized data.
 * This was formerly done by plain php code and was time and memory consuming.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NormalizedDataQueryGeneratorInterface
{
    /**
     * Generate the query to update concerned products
     *
     * @param object $entity
     * @param string $field
     * @param string $oldValue
     * @param string $newValue
     *
     * @return array
     */
    public function generateQuery($entity, $field, $oldValue, $newValue);

    /**
     * Test if the query generator support the given modification on the given entity
     *
     * @param object $entity
     * @param string $field
     *
     * @return bool
     */
    public function supports($entity, $field);
}
