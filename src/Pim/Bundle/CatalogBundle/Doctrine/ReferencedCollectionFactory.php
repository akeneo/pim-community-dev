<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Factory of referenced collection
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferencedCollectionFactory
{
    /** @var ObjectManager */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a referenced collection
     *
     * @param string $entityClass
     * @param array  $identifiers
     *
     * @return ReferencedCollection
     */
    public function create($entityClass, $identifiers)
    {
        if (null === $identifiers) {
            $identifiers = [];
        }

        if (!is_array($identifiers)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expecting identifiers to be null or type array, got "%s"',
                    is_object($identifiers) ? get_class($identifiers) : $identifiers
                )
            );
        }

        return new ReferencedCollection(
            $entityClass,
            $identifiers,
            $this->objectManager
        );
    }
}
