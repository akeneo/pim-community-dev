<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Factory of referenced collection
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferencedCollectionFactory
{
    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Create a referenced collection
     *
     * @param string $entityClass
     * @param array  $identifiers
     * @param object $document
     *
     * @return ReferencedCollection
     */
    public function create($entityClass, $identifiers, $document)
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

        $coll = new ReferencedCollection(
            $entityClass,
            $identifiers,
            $this->registry->getManagerForClass($entityClass),
            $this->registry->getManagerForClass(get_class($document))->getUnitOfWork()
        );
        $coll->setOwner($document);

        return $coll;
    }
}
