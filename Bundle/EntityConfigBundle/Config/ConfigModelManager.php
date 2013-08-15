<?php

namespace Oro\Bundle\EntityConfigBundle\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

use Oro\Bundle\EntityConfigBundle\Entity\AbstractConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Proxy\ServiceProxy;
use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

class ConfigModelManager
{
    /**
     * mode of config model
     */
    const MODE_DEFAULT  = 'default';
    const MODE_HIDDEN   = 'hidden';
    const MODE_READONLY = 'readonly';

    /**
     * @var AbstractConfigModel[]|ArrayCollection
     */
    protected $localCache;

    /**
     * @var ServiceProxy
     */
    protected $proxyEm;

    public function __construct(ServiceProxy $proxyEm)
    {
        $this->localCache = new ArrayCollection;
        $this->proxyEm    = $proxyEm;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->proxyEm->getService();
    }


    /**
     * @return bool
     */
    public function checkDatabase()
    {
        $tables = $this->getEntityManager()->getConnection()->getSchemaManager()->listTableNames();
        $table  = $this->getEntityManager()->getClassMetadata(EntityConfigModel::ENTITY_NAME)->getTableName();

        return in_array($table, $tables);
    }

    /**
     * @param      $className
     * @param null $fieldName
     * @return null|AbstractConfigModel
     */
    public function findModel($className, $fieldName = null)
    {
        $cacheKey = $className . $fieldName;

        if ($this->localCache->containsKey($cacheKey)) {
            return $this->localCache->get($cacheKey);
        }

        $entityConfigModelRepo = $this->getEntityManager()->getRepository(EntityConfigModel::ENTITY_NAME);

        $entity = $entityConfigModelRepo->findOneBy(array('className' => $className));

        if ($fieldName) {
            $fieldConfigModelRepo = $this->getEntityManager()->getRepository(FieldConfigModel::ENTITY_NAME);

            $result = $fieldConfigModelRepo->findOneBy(
                array(
                    'entity'    => $entity,
                    'fieldName' => $fieldName
                )
            );
        } else {
            $result = $entity;
        }

        if ($result) {
            $this->localCache->set($cacheKey, $result);
        }

        return $result;
    }

    /**
     * @param      $className
     * @param null $fieldName
     * @return null|AbstractConfigModel
     * @throws RuntimeException
     * @throws RuntimeException
     */
    public function getModel($className, $fieldName = null)
    {
        if (!$model = $this->findModel($className, $fieldName)) {
            $message = $fieldName
                ? sprintf('FieldConfigModel "%s","%s" is not found ', $className, $fieldName)
                : sprintf('EntityConfigModel "%s" is not found ', $className);

            throw new RuntimeException($message);
        }

        return $model;
    }

    /**
     * @param ConfigIdInterface $configId
     * @return AbstractConfigModel
     */
    public function getModelByConfigId(ConfigIdInterface $configId)
    {
        $fieldName = $configId instanceof FieldConfigId ? $configId->getFieldName() : null;

        return $this->getModel($configId->getClassName(), $fieldName);
    }

    /**
     * @param null $className
     * @return AbstractConfigModel[]
     */
    public function getModels($className = null)
    {
        if ($className) {
            return $this->getModel($className)->getFields()->toArray();
        } else {
            $entityConfigModelRepo = $this->getEntityManager()->getRepository(EntityConfigModel::ENTITY_NAME);

            return (array) $entityConfigModelRepo->findAll();
        }
    }

    /**
     * @param string $className
     * @param string $mode
     * @throws \InvalidArgumentException
     * @return EntityConfigModel
     */
    public function createEntityModel($className, $mode = self::MODE_DEFAULT)
    {
        if (!in_array($mode, array(self::MODE_DEFAULT, self::MODE_READONLY))) {
            throw new \InvalidArgumentException(
                sprintf('EntityConfigModel give invalid parameter "mode" : "%s"', $mode)
            );
        }

        $entityModel = new EntityConfigModel($className);
        $entityModel->setMode($mode);

        $this->localCache->set($className, $entityModel);

        return $entityModel;
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @param string $fieldType
     * @param string $mode
     * @throws \InvalidArgumentException
     * @return FieldConfigModel
     */
    public function createFieldModel($className, $fieldName, $fieldType, $mode = self::MODE_DEFAULT)
    {
        if (!in_array($mode, array(self::MODE_DEFAULT, self::MODE_HIDDEN, self::MODE_READONLY))) {
            throw new \InvalidArgumentException(
                sprintf('FieldConfigModel give invalid parameter "mode" : "%s"', $mode)
            );
        }

        /** @var EntityConfigModel $entityModel */
        $entityModel = $this->getModel($className);

        $fieldModel = new FieldConfigModel($fieldName, $fieldType);
        $fieldModel->setMode($mode);
        $entityModel->addField($fieldModel);

        $this->localCache->set($className . $fieldName, $fieldModel);

        return $fieldModel;
    }
}
