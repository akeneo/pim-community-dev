<?php

namespace Oro\Bundle\EntityExtendBundle\Tools;

use Doctrine\ORM\EntityManager;

class Schema
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $backend;

    /**
     * @param EntityManager $em
     * @param string        $backend
     */
    public function __construct(EntityManager $em, $backend)
    {
        $this->em      = $em;
        $this->backend = $backend;
    }

    /**
     * @return bool
     */
    public function checkDynamicBackend()
    {
        try {
            $this->exec("CREATE TABLE `__check_table__`(id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id))");
            $this->exec("DROP TABLE `__check_table__`");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $statement
     * @return int
     */
    protected function exec($statement)
    {
        return $this->em->getConnection()->exec($statement);
    }
}
