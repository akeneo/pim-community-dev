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
class CurrentNumberRegistry
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * CurrentNumberRegistry constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * @param int $number
     * @param string $code
     * @throws \Doctrine\DBAL\DBALException
     */
    public function incrementAttributeNumber(int $number, string $code)
    {
        $this->connection->executeQuery('UPDATE pim_limit SET value = value + :number WHERE code = :code', [
            'number' => $number,
            'code' => $code
        ]);
    }

    /**
     * @param int $number
     * @param string $code
     * @throws \Doctrine\DBAL\DBALException
     */
    public function decrementAttributeNumber(int $number, string $code)
    {
        $this->connection->executeQuery('UPDATE pim_limit SET value = value - :number WHERE code = :code', [
            'number' => $number,
            'code' => $code
        ]);
    }
}
