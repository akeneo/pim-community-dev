<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\Collections\Collection;
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

        $coll = new ReferencedCollection($entityClass, $identifiers, $this->registry);
        $coll->setOwner($document);

        return $coll;
    }

    /**
     * Create a reference collection from an already existed collection
     *
     * @param string     $entityClass
     * @param object     $document
     * @param Collection $collection
     *
     * @return ReferencedCollection
     */
    public function createFromCollection($entityClass, $document, Collection $collection)
    {
        $coll = new ReferencedCollection($entityClass, [], $this->registry);
        $coll->setOwner($document);
        $coll->populate($collection);

        return $coll;
    }
}
