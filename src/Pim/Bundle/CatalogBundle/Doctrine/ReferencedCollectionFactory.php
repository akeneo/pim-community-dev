<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Pim\Bundle\CatalogBundle\Doctrine\ReferencedCollection;
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
    public function create($entityClass, array $identifiers)
    {
        return new ReferencedCollection(
            $entityClass, $identifiers, $this->objectManager
        );
    }
}
