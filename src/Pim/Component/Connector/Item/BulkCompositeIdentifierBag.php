<?php

namespace Pim\Component\Connector\Item;

/**
 * A bag of composite identifiers (array of identifier values) that can be used during bulk operations.
 * It supports entities like Attribute Option identifiers which key is attribute code and attribute option code.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkCompositeIdentifierBag implements BulkIdentifierBagInterface
{
    /** @var array */
    protected $compositeIdentifiers = [];

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException when the identifier is not an array (composite key)
     */
    public function add($compositeIdentifier)
    {
        if (!is_array($compositeIdentifier)) {
            throw new \InvalidArgumentException(sprintf(
                'The identifier "%s" is not a composite key (an array).',
                $compositeIdentifier
            ));
        }

        if ($this->has($compositeIdentifier)) {
            throw new \LogicException(sprintf(
                'The composite identifier "%s" is already contained in the bag.',
                join(', ', $compositeIdentifier)
            ));
        }

        $this->compositeIdentifiers[] = $compositeIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function has($compositeIdentifier)
    {
        $isPresent = false;

        foreach ($this->compositeIdentifiers as $compositeIdentifierBag) {
            $isPresent = empty(array_diff($compositeIdentifier, $compositeIdentifierBag)) || $isPresent;
        }

        return $isPresent;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->compositeIdentifiers = [];
    }
}
