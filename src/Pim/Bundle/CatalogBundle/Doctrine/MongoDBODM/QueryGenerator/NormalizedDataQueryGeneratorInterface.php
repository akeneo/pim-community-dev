<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* NormalizedData query generator interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NormalizedDataQueryGeneratorInterface
{
    /**
     * Generate the query to update concerned products
     * @param mixed  $entity
     * @param string $field
     * @param string $oldValue
     * @param string $newValue
     *
     * @return array
     */
    public function generateQuery($entity, $field, $oldValue, $newValue);

    /**
     * Test if the query generator support the given modification on the given entity
     * @param string $entity
     * @param string $field
     *
     * @return boolean
     */
    public function supports($entity, $field);
}
