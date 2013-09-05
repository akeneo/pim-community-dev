<?php

namespace Oro\Bundle\EntityExtendBundle\Tools;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigIdInterface;
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
     * @param OroEntityManager $em
     * @param string        $backend
     */
    public function __construct(OroEntityManager $em, $backend)
    {
        $this->em            = $em;
        $this->backend       = $backend;
        $this->extendManager = $em->getExtendManager();
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
     * @param $fieldId FieldConfigIdInterface
     * @return bool
     */
    public function checkFieldIsSystem(FieldConfigIdInterface $fieldId)
    {
        $isSystem = false;
        $metadata = $this->em->getClassMetadata($fieldId->getClassName());
        if (in_array($fieldId->getFieldName(), $metadata->fieldNames)) {
            $isSystem = true;
        }

        return $isSystem;
    }

    /**
     * @param $fieldId FieldConfigIdInterface
     * @return bool
     */
    public function checkFieldCanDelete(FieldConfigIdInterface $fieldId)
    {
        $canDelete = false;

        if ($fieldId->getClassName()
            && $fieldId->getFieldName()
            && !$this->checkFieldIsSystem($fieldId)
        ) {
            $extendClass = $this->extendManager->getExtendClass($fieldId->getClassName());

            /** @var QueryBuilder $builder */
            $builder = $this->em->getRepository($extendClass)->createQueryBuilder('ex');
            $builder->select('MAX(ex.' . $fieldId->getFieldName() . ')');

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
