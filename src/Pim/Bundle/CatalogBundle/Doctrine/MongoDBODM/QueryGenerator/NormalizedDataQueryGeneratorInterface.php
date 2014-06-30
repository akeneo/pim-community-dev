<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

/**
* NormalizedData query generator interface
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
