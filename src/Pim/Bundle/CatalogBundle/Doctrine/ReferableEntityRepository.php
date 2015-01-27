<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * Base repository for entities with a code unique index
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.4
 */
class ReferableEntityRepository extends EntityRepository implements
    ReferableEntityRepositoryInterface,
    IdentifiableObjectRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(array('code' => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return array('code');
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.4
     */
    public function getReferenceProperties()
    {
        return $this->getIdentifierProperties();
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.4
     */
    public function findByReference($code)
    {
        return $this->findOneByIdentifier($code);
    }
}
