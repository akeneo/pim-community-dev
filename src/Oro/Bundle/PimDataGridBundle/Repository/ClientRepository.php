<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ClientRepository extends EntityRepository implements DatagridRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('cl');

        $qb->select('cl.label as label');
        $qb->addSelect('CONCAT(cl.id, \'_\',cl.randomId, \'|\', cl.secret) as credentials');
        $qb->addSelect('CONCAT(cl.id, \'_\',cl.randomId) as publicId');

        return $qb;
    }
}
