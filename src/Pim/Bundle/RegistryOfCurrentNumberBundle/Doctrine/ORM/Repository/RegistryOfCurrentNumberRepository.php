<?php

namespace Pim\Bundle\RegistryOfCurrentNumberBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\RegistryOfCurrentNumberBundle\Repository\RegistryOfCurrentNumberRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;

/**
 * RegistryOfCurrentNumber repository
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegistryOfCurrentNumberRepository extends EntityRepository implements RegistryOfCurrentNumberRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }
}
