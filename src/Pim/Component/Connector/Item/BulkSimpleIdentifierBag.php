<?php

namespace Pim\Component\Connector\Item;

/**
 * A bag of simple identifiers that can be used during bulk operations.
 *
 * This class also supports composite keys (as implemented in the attribute options for instance).
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkSimpleIdentifierBag implements BulkIdentifierBagInterface
{
    /** @var array */
    protected $identifiers = [];

    /**
     * {@inheritdoc}
     */
    public function add($identifier)
    {
        if ($this->has($identifier)) {
            throw new \LogicException(sprintf('The identifier "%s" is already contained in the bag.', $identifier));
        }

        $this->identifiers[] = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function has($identifier)
    {
        return in_array($identifier, $this->identifiers);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->identifiers = [];
    }
}
