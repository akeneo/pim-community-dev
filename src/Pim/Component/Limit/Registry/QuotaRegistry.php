<?php

namespace Pim\Component\Limit\Registry;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuotaRegistry
{
    /**
     * @var Connection $connection
     */
    private $connection;

    /**
     * @var array
     */
    private $limits;

    /**
     * QuotaRegistry constructor.
     * @param EntityManager $em
     * @param array $limits
     */
    public function __construct(EntityManager $em, array $limits)
    {
        $this->connection = $em->getConnection();
        $this->limits = $limits;
    }

    /**
     * @param int $number
     * @return bool
     */
    public function isLimitReachedForAttribute(int $number)
    {
        $results = $this->connection->fetchColumn('SELECT (value + :number) > :limit
            FROM pim_limit 
            WHERE code = :code', [
                'code' => 'ATTRIBUTE_NUMBER',
                'number' => $number,
                'limit' => $this->limits['attribute_number']
        ]);

        return $results;
    }
}
