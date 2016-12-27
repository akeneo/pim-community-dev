<?php

namespace Pim\Component\Connector;

/**
 * A bag of identifiers that can be used during bulk operations.
 *
 * Typically, during an import, there can be several items with the same identifiers in the same bulk.
 * Those items are not detected as duplications and lead to errors in database.
 * This bag aims to store them momentarily during the period of the bulk.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkIdentifierBag
{
    private $identifiers = [];

    /**
     * @param string $identifier
     *
     * @throws \LogicException when the identifier is already in the bag
     */
    public function add($identifier)
    {
        if ($this->has($identifier)) {
            throw new \LogicException(sprintf('The identifier "%s" is already contained in the bag.', $identifier));
        }

        $this->identifiers[] = $identifier;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return in_array($identifier, $this->identifiers);
    }

    /**
     * Empty the bag
     */
    public function reset()
    {
        $this->identifiers = [];
    }
}
