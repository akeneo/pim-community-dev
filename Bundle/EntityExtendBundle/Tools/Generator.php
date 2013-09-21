<?php

namespace Oro\Bundle\EntityExtendBundle\Tools;

use Symfony\Component\Yaml\Yaml;

use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\PhpClass;
use CG\Generator\PhpMethod;
use CG\Generator\PhpParameter;
use CG\Generator\PhpProperty;
use CG\Generator\Writer;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;

use Oro\Bundle\EntityExtendBundle\Mapping\ExtendClassMetadataFactory;
use Oro\Bundle\EntityExtendBundle\Exception\RuntimeException;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class Generator
{
    const ENTITY = 'Extend\\Entity\\';
    const PREFIX = 'field_';

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var string
     */
    protected $configDir;

    /**
     * @var string
     */
    protected $backupDir;

    /**
     * @var ExtendManager
     */
    protected $extendManager;

    /**
     * @var OroEntityManager
     */
    protected $em;

    /**
     * @var Writer
     */
    protected $writer = null;

    /**
     * @param OroEntityManager $em
     * @param string           $cacheDir
     * @param string           $configDir
     * @param string           $backupDir
     */
    public function __construct(OroEntityManager $em, $cacheDir, $configDir, $backupDir)
    {
        $this->cacheDir      = $cacheDir;
        $this->configDir     = $configDir;
        $this->backupDir     = $backupDir;
        $this->em            = $em;
        $this->extendManager = $em->getExtendManager();
    }

    public function dump()
    {
        $yml     = array();
        $configs = $this->extendManager->getConfigProvider()->getConfigs();
        foreach ($configs as $config) {
            if ($config->is('is_extend')) {
                $yml[] = $this->dumpByConfig($config);
            }
        }

        $this->extendManager->getConfigProvider()->flush();

        file_put_contents(
            $this->backupDir . '/dump.yml',
            Yaml::dump($yml, 6)
        );
    }

    protected function dumpByConfig(ConfigInterface $entityConfig)
    {
        $configProvider = $this->extendManager->getConfigProvider();
        $className      = $entityConfig->getId()->getClassName();
        if (strpos($className, self::ENTITY) !== false) {
            $entityName = $className;
            $type       = 'Custom';
            $doctrine   = array(
                $entityName => array(
                    'type'       => 'entity',
                    'table'      => 'oro_extend_' . strtolower(str_replace('\\', '', $entityName)),
                    'fields'     => array(
                        'id' => array(
                            'type'      => 'integer',
                            'id'        => true,
                            'generator' => array(
                                'strategy' => 'AUTO'
                            )
                        )
                    ),
                    'oneToMany'  => array(),
                    'manyToOne'  => array(),
                    'manyToMany' => array(),
                )
            );
        } else {
            $entityName = $entityConfig->get('extend_class');
            $type       = 'Extend';
            $doctrine   = array(
                $entityName => array(
                    'type'       => 'mappedSuperclass',
                    'fields'     => array(),
                    'oneToMany'  => array(),
                    'manyToOne'  => array(),
                    'manyToMany' => array(),
                )
            );
        }

        $entityState = $entityConfig->get('state');

        if ($fieldConfigs = $configProvider->getConfigs($className)) {
            foreach ($fieldConfigs as $fieldConfig) {
                if ($fieldConfig->is('extend')) {
                    $fieldName = self::PREFIX . $fieldConfig->getId()->getFieldName();
                    $fieldType = self::PREFIX . $fieldConfig->getId()->getFieldType();

                    $doctrine[$entityName]['fields'][$fieldName]['code']     = $fieldName;
                    $doctrine[$entityName]['fields'][$fieldName]['type']     = $fieldType;
                    $doctrine[$entityName]['fields'][$fieldName]['nullable'] = true;

                    $doctrine[$entityName]['fields'][$fieldName]['length']    = $fieldConfig->get('length');
                    $doctrine[$entityName]['fields'][$fieldName]['precision'] = $fieldConfig->get('precision');
                    $doctrine[$entityName]['fields'][$fieldName]['scale']     = $fieldConfig->get('scale');
                }

                if ($fieldConfig->get('owner') != ExtendManager::OWNER_SYSTEM
                    && $fieldConfig->get('state') != ExtendManager::STATE_DELETED
                ) {
                    $fieldConfig->set('state', ExtendManager::STATE_ACTIVE);
                }

                if ($fieldConfig->get('state') == ExtendManager::STATE_DELETED) {
                    $fieldConfig->set('is_deleted', true);
                }

                $configProvider->persist($fieldConfig);
            }
        }

        $entityConfig->set('state', $entityState);
        if ($entityConfig->get('state') == ExtendManager::STATE_DELETED) {
            $entityConfig->set('is_deleted', true);
        } else {
            $entityConfig->set('state', ExtendManager::STATE_ACTIVE);
        }

        $configProvider->persist($entityConfig);

        $result = array(
            'class'    => $className,
            'entity'   => $entityName,
            'type'     => $type,
            'doctrine' => $doctrine,
        );

        return $result;
    }

    /**
     * @param      $className
     * @param bool $save
     * @return array
     */
    protected function generateExtendYaml($className, $save = false)
    {
        $config      = $this->extendManager->getConfigProvider()->getConfig($className);
        $extendClass = $config->get('extend_class');

        $yml = array(
            $extendClass => array(
                'type'       => 'mappedSuperclass',
                'fields'     => array(),
                'oneToMany'  => array(),
                'manyToOne'  => array(),
                'manyToMany' => array(),
            )
        );

        $this->generateYamlMethods($extendClass, $className, $yml);

        if ($save) {
            $ymlPathDist = $this->cacheDir . '/' . str_replace('\\', '/', $extendClass) . '.orm.yml';
            file_put_contents($ymlPathDist, Yaml::dump($yml, 5));
        }

        return $yml;
    }

    public function generate($className)
    {
        if (strpos($className, self::ENTITY) !== false) {
            $this->generateCustomClass($className);
            $this->generateCustomYaml($className);
        } else {
            if ($this->em->isExtendEntity($className)) {
                $this->generateExtendClass($className);
                $this->generateExtendYaml($className);
            }
        }

        $this->generateValidation($className);
    }

    public function generateAll()
    {
        if (!file_exists($this->backupDir . '/dump.yml')) {
            return;
        }

        $data = Yaml::parse(file_get_contents($this->backupDir . '/dump.yml'));

        //$this->generateAlias($data);

        //var_dump($data);
//        $configIds = $this->extendManager->getConfigProvider()->getIds();
//        foreach ($configIds as $configId) {
//            $this->generate($configId->getClassName());
//        }

        /** @var ExtendClassMetadataFactory $metadataFactory */
        $metadataFactory = $this->em->getMetadataFactory();
        $metadataFactory->clearCache();
    }


    public function generateAlias($data)
    {
        $aliases = array();

        foreach ($data as $item) {
            if ($item['type'] == 'Extend') {
                $aliases[$item['entity']] = get_parent_class($item['class']);
            }
        }

        file_put_contents($this->cacheDir . '/Extend/Entity/alias.yml', Yaml::dump($aliases));
    }

    protected function generateExtendClass($className)
    {
        $this->writer = new Writer();

        $config      = $this->extendManager->getConfigProvider()->getConfig($className);
        $extendClass = $config->get('extend_class');
        $parentClass = $config->get('inheritance');

        $class = PhpClass::create($extendClass)
            ->setParentClassName($parentClass)
            ->setInterfaceNames(array('Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface'));

        $this->generateClassMethods($className, $class);

        $filePath = $this->cacheDir . '/' . str_replace('\\', '/', $extendClass) . '.php';

        $strategy = new DefaultGeneratorStrategy();

        file_put_contents($filePath, "<?php\n\n" . $strategy->generate($class));
    }

    /**
     * @param $className
     * @return string
     */
    protected function generateCustomClass($className)
    {
        $this->writer = new Writer();

        $class = PhpClass::create($className)
            ->setInterfaceNames(array('Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface'))
            ->setProperty(PhpProperty::create('id')->setVisibility('protected'))
            ->setMethod($this->generateClassMethod('getId', 'return $this->id;'));

        $this->generateClassMethods($className, $class);

        $strategy = new DefaultGeneratorStrategy();

        $classNameArray = explode('\\', $className);
        file_put_contents(
            $this->cacheDir . '/Extend/Entity/' . array_pop($classNameArray) . '.php',
            "<?php\n\n" . $strategy->generate($class)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param $className
     */
    protected function generateValidation($className)
    {
        $configProvider = $this->extendManager->getConfigProvider();

        /** Constraints */
        $yml['constraints'] = array();

        /** properties */
        $yml['properties'] = array();

        $uniqueKeys = $configProvider->getConfig($className)->get('unique_key');
        if ($uniqueKeys) {
            foreach ($uniqueKeys['keys'] as $keys) {
                $yml['constraints'][]['ExtendUniqueEntity'] = $keys['key'];
            }
        }

        if ($fieldIds = $configProvider->getIds($className)) {
            foreach ($fieldIds as $fieldId) {
                if ($configProvider->getConfigById($fieldId)->is('extend')) {
                    $config    = $configProvider->getConfigById($fieldId);
                    $fieldName = $fieldId->getfieldName();

                    switch ($fieldId->getFieldType()) {
                        case 'integer':
                        case 'smallint':
                        case 'bigint':
                            $yml['properties'][$fieldName][] = array(
                                'Regex' => '/\d+/'
                            );
                            break;
                        case 'string':
                            $yml['properties'][$fieldName][] = array(
                                'Length' => array('max' => $config->get('length'))
                            );
                            break;
                        case 'decimal':
                            $yml['properties'][$fieldName][] = array(
                                'Regex' => '/\d{1,' . $config->get('precision') . '}\.\d{1,' . $config->get(
                                    'scale'
                                ) . '}/'
                            );
                            break;
                        case 'date':
                            $yml['properties'][$fieldName][] = array(
                                'Date' => '~'
                            );
                            break;
                        case 'time':
                            $yml['properties'][$fieldName][] = array(
                                'Time' => '~'
                            );
                            break;
                        case 'datetime':
                            $yml['properties'][$fieldName][] = array(
                                'DateTime' => '~'
                            );
                            break;
                        case 'boolean':
                        case 'text':
                        case 'float':
                            break;
                    }
                }
            }
        }

        $validators[$className] = $yml;

        file_put_contents(
            $this->cacheDir . '/Extend/Validator/' . str_replace('\\', '.', $className) . '.yml',
            Yaml::dump($validators, 5)
        );

    }

    /**
     * @param $className
     * @param $yml
     */
    protected function generateYamlMethods($extendClassName, $className, &$yml)
    {
        $configProvider = $this->extendManager->getConfigProvider();
        if ($fieldIds = $configProvider->getIds($className)) {
            foreach ($fieldIds as $fieldId) {
                if ($configProvider->getConfigById($fieldId)->is('extend')) {
                    $fieldName = self::PREFIX . $fieldId->getFieldName();

                    $yml[$extendClassName]['fields'][$fieldName]['code']     = $fieldName;
                    $yml[$extendClassName]['fields'][$fieldName]['type']     = $fieldId->getFieldType();
                    $yml[$extendClassName]['fields'][$fieldName]['nullable'] = true;

                    $fieldConfig = $configProvider->getConfigById($fieldId);

                    $yml[$extendClassName]['fields'][$fieldName]['length']    = $fieldConfig->get('length');
                    $yml[$extendClassName]['fields'][$fieldName]['precision'] = $fieldConfig->get('precision');
                    $yml[$extendClassName]['fields'][$fieldName]['scale']     = $fieldConfig->get('scale');
                }
            }
        }
    }

    /**
     * @param $className
     * @param $class
     */
    protected function generateClassMethods($className, &$class)
    {
        $configProvider = $this->extendManager->getConfigProvider();

        $toArray = '';
        if ($fieldIds = $configProvider->getIds($className)) {
            foreach ($fieldIds as $fieldId) {
                if ($configProvider->getConfigById($fieldId)->is('extend')) {
                    $fieldName  = self::PREFIX . $fieldId->getFieldName();
                    $methodName = $fieldId->getFieldName();
                    $class
                        ->setProperty(PhpProperty::create($fieldName)->setVisibility('protected'))
                        ->setMethod(
                            $this->generateClassMethod(
                                'get' . ucfirst(Inflector::camelize($methodName)),
                                'return $this->' . $fieldName . ';'
                            )
                        )
                        ->setMethod(
                            $this->generateClassMethod(
                                'set' . ucfirst(Inflector::camelize($methodName)),
                                '$this->' . $fieldName . ' = $value; return $this;',
                                array('value')
                            )
                        );
                } else {
                    $fieldName = $fieldId->getFieldName();
                }
                $toArray .= '    \'' . $fieldName . '\' => $this->' . $fieldName . ',' . "\n";
            }
        }

        $class
            ->setMethod(
                $this->generateClassMethod(
                    '__toArray',
                    'return array(' . $toArray . "\n" . ');'
                )
            )
            ->setMethod(
                $this->generateClassMethod(
                    '__fromArray',
                    'foreach ($values as $key => $value) {$this->{\'set\'.ucfirst($key)}($value);}',
                    array('values')
                )
            );
    }

    /**
     * @param       $methodName
     * @param       $methodBody
     * @param array $methodArgs
     * @return $this
     */
    protected function generateClassMethod($methodName, $methodBody, $methodArgs = array())
    {
        $this->writer->reset();
        $method = PhpMethod::create($methodName)->setBody(
            $this->writer->write($methodBody)->getContent()
        );

        if (count($methodArgs)) {
            foreach ($methodArgs as $arg) {
                $method->addParameter(PhpParameter::create($arg));
            }
        }

        return $method;
    }

    /**
     * @param $cachePath
     * @param $subPath
     * @return string
     * @throws \Oro\Bundle\EntityExtendBundle\Exception\RuntimeException
     */
    protected function createFolder($cachePath, $subPath)
    {
        $dir = $cachePath . implode(DIRECTORY_SEPARATOR, $subPath) . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            if (false === mkdir($dir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
            }
        }

        return $dir;
    }
}
