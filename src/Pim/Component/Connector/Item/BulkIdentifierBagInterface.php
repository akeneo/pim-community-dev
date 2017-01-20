<?php

namespace Pim\Component\Connector\Item;

/**
 * A bag of identifiers that can be used during bulk operations.
 *
 * Typically, during an import, there can be several items with the same identifiers in the same bulk.
 * Those items are not detected as duplications and lead to errors in database.
 * This bag aims to store them momentarily during the period of the bulk.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BulkIdentifierBagInterface
{
    /**
     * Method that adds an identifier to the list of identifiers
     *
     * @param mixed $identifier
     *
     * @throws \LogicException when the identifier is already in the bag
     */
    public function add($identifier);

    /**
     * Checks if the given identifier exists in the bag.
     * In the case of composite keys it checks if all the values of the composite keys isn't in the list of composite
     * keys.
     *
     * @param $identifier
     *
     * @return bool
     */
    public function has($identifier);

    /**
     * Empty the bag
     */
    public function reset();
}
