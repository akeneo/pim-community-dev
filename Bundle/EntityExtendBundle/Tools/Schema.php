<?php

namespace Oro\Bundle\EntityExtendBundle\Tools;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

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
     * @var ExtendManager
     */
    protected $extendManager;

    /**
     * @param EntityManager $em
     * @param $backend
     * @param ExtendManager $extendManager
     */
    public function __construct(EntityManager $em, $backend, ExtendManager $extendManager)
    {
        $this->em            = $em;
        $this->backend       = $backend;
        $this->extendManager = $extendManager;
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

    public function checkIsSynchronized($table)
    {
        try {

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $field FieldConfig
     * @return bool
     */
    public function checkFieldIsSystem(FieldConfig $field)
    {
        $isSystem = false;
        $metadata = $this->em->getClassMetadata($field->getClassName());
        if (in_array($field->getCode(), $metadata->fieldNames)) {
            $isSystem =  true;
        }

        return $isSystem;
    }

    /**
     * @param $field FieldConfig
     * @return bool
     */
    public function checkFieldCanDelete(FieldConfig $field)
    {
        $canDelete = false;

        if ($field->getClassName()
            && $field->getCode()
            && !$this->checkFieldIsSystem($field)
        ) {
            $extendClass = $this->extendManager->getExtendClass($field->getClassName());

            /** @var QueryBuilder $builder */
            $builder = $this->em->getRepository($extendClass)->createQueryBuilder('ex');
            $builder->select('MAX(ex.'.$field->getCode(). ')');

            if (!$builder->getQuery()->getSingleResult(AbstractQuery::HYDRATE_SINGLE_SCALAR)) {
                $canDelete = true;
            }
        }

        return $canDelete;
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
