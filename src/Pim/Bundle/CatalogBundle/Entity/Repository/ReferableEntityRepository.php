<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;

/**
 * Base repository for entities with a code unique index
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferableEntityRepository extends EntityRepository implements ReferableEntityRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return ['code'];
    }
}
