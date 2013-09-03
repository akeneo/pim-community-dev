<?php

namespace Oro\Bundle\EntityExtendBundle\Tools\Generator;

use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Symfony\Component\Yaml\Yaml;

use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\PhpClass;
use CG\Generator\PhpMethod;
use CG\Generator\PhpParameter;
use CG\Generator\PhpProperty;
use CG\Generator\Writer;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class Generator
{
    /**
     * @var string
     */
    protected $backend;

    /**
     * @var string
     */
    protected $entityCacheDir;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var Writer
     */
    protected $writer = null;

    /**
     * @param ConfigProvider $configProvider
     * @param                $backend
     * @param                $entityCacheDir
     */
    public function __construct(ConfigProvider $configProvider, $backend, $entityCacheDir)
    {
        $this->backend        = $backend;
        $this->entityCacheDir = $entityCacheDir;
        $this->configProvider = $configProvider;
    }

    /**
     * @param      $entityName
     * @param bool $force
     * @param bool $extend
     */
    public function checkEntityCache($entityName, $force = false, $extend = true)
    {
        $extendClass = $this->generateExtendClassName($entityName);
        $proxyClass  = $this->generateProxyClassName($entityName);
        $validators  = $this->entityCacheDir . DIRECTORY_SEPARATOR . 'validator.yml';

        if ((!class_exists($extendClass) || !class_exists($proxyClass)) || $force) {
            /** write Dynamic class */
            file_put_contents(
                $this->entityCacheDir . DIRECTORY_SEPARATOR . str_replace(
                    '\\',
                    DIRECTORY_SEPARATOR,
                    $extendClass
                ) . '.php',
                "<?php\n\n" . $this->generateDynamicClass($entityName, $extendClass)
            );

            /** write Dynamic yml */
            file_put_contents(
                $this->entityCacheDir . DIRECTORY_SEPARATOR . str_replace(
                    '\\',
                    DIRECTORY_SEPARATOR,
                    $extendClass
                ) . '.orm.yml',
                Yaml::dump($this->generateDynamicYml($entityName, $extendClass), 5)
            );

            /** write Proxy class */
            file_put_contents(
                $this->entityCacheDir . DIRECTORY_SEPARATOR . str_replace(
                    '\\',
                    DIRECTORY_SEPARATOR,
                    $proxyClass
                ) . '.php',
                "<?php\n\n" . $this->generateProxyClass($entityName, $proxyClass, $extend)
            );
        }

        if (!file_exists($validators)) {
            file_put_contents($validators, '');
        }

        file_put_contents(
            $validators,
            Yaml::dump($this->generateValidator($entityName, Yaml::parse($validators)))
        );
    }

    /**
     * @param $entityName
     * @return string
     */
    public function generateExtendClassName($entityName)
    {
        return 'Extend\\Entity\\' . $this->backend . '\\' . $this->generateClassName($entityName);
    }

    /**
     * @param $entityName
     * @return string
     */
    public function generateProxyClassName($entityName)
    {
        return 'Extend\\Proxy\\' . $this->generateClassName($entityName);
    }

    /**
     * @param $entityName
     * @return string
     */
    protected function generateClassName($entityName)
    {
        return str_replace('\\', '', $entityName);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param $entityName
     * @param $validators
     * @return mixed
     */
    protected function generateValidator($entityName, $validators)
    {
        /** Constraints */
        $yml['constraints'] = array();

        $uniqueKeys = $this->configProvider->getConfig($entityName)->get('unique_key');
        if ($uniqueKeys) {
            foreach ($uniqueKeys['keys'] as $keys) {
                $yml['constraints'][]['ExtendUniqueEntity'] = $keys['key'];
            }
        }

        /** properties */
        $yml['properties'] = array();

        if ($fieldIds = $this->configProvider->getIds($entityName)) {
            foreach ($fieldIds as $fieldId) {
                if ($this->configProvider->getConfigById($fieldId)->is('is_extend')) {
                    $config = $this->configProvider->getConfigById($fieldId);
                    switch ($fieldId->getFieldType()) {
                        case 'integer':
                        case 'smallint':
                        case 'bigint':
                            $yml['properties'][$fieldId->getfieldName()][] = array(
                                'Regex' => '/\d+/'
                            );
                            break;
                        case 'string':
                            $yml['properties'][$fieldId->getfieldName()][] = array(
                                'Length' => array('max' => $config->get('length'))
                            );
                            break;
                        case 'decimal':
                            $yml['properties'][$fieldId->getfieldName()][] = array(
                                'Regex' => '/\d{1,' . $config->get('precision') . '}\.\d{1,' . $config->get(
                                    'scale'
                                ) . '}/'
                            );
                            break;
                        case 'date':
                            $yml['properties'][$fieldId->getfieldName()][] = array(
                                'Date' => '~'
                            );
                            break;
                        case 'time':
                            $yml['properties'][$fieldId->getfieldName()][] = array(
                                'Time' => '~'
                            );
                            break;
                        case 'datetime':
                            $yml['properties'][$fieldId->getfieldName()][] = array(
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

        $validators[$entityName] = $yml;

        return $validators;
    }

    /**
     * @param $entityName
     * @param $extendClass
     * @return array
     */
    protected function generateDynamicYml($entityName, $extendClass)
    {
        $yml = array(
            $extendClass => array(
                'type'     => 'entity',
                'table'    => 'oro_extend_' . strtolower(str_replace('\\', '', $entityName)),
                'oneToOne' => array(
                    '__extend__parent' => array(
                        'targetEntity' => $entityName,
                        'joinColumn'   => array(
                            'name'                 => '__extend__parent_id',
                            'referencedColumnName' => 'id',
                            'nullable'             => true,
                        ),
                    ),
                ),
                'fields'   => array(
                    'id' => array(
                        'type'      => 'integer',
                        'id'        => true,
                        'generator' => array(
                            'strategy' => 'AUTO'
                        )
                    )
                )
            )
        );

        if ($fieldIds = $this->configProvider->getIds($entityName)) {
            foreach ($fieldIds as $fieldId) {
                if ($this->configProvider->getConfigById($fieldId)->is('is_extend')
                    //&& $this->configProvider->getConfigById($fieldId)->get('state') != ExtendManager::STATE_DELETED
                ) {
                    $yml[$extendClass]['fields'][$fieldId->getFieldName()]['code'] = $fieldId->getFieldName();
                    $yml[$extendClass]['fields'][$fieldId->getFieldName()]['type'] = $fieldId->getFieldType();

                    $fieldConfig = $this->configProvider->getConfigById($fieldId);

                    $yml[$extendClass]['fields'][$fieldId->getFieldName()]['length']    = $fieldConfig->get('length');
                    $yml[$extendClass]['fields'][$fieldId->getFieldName()]['precision'] = $fieldConfig->get(
                        'precision'
                    );
                    $yml[$extendClass]['fields'][$fieldId->getFieldName()]['scale']     = $fieldConfig->get('scale');
                }
            }
        }

        return $yml;
    }

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
     * Prepare Dynamic class
     * @param $entityName
     * @param $className
     * @return $this
     */
    protected function generateDynamicClass($entityName, $className)
    {
        $this->writer = new Writer();

        $class = PhpClass::create($this->generateClassName($entityName))
            ->setName($className)
            ->setInterfaceNames(array('Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface'))
            ->setProperty(PhpProperty::create('id')->setVisibility('protected'))
            ->setProperty(PhpProperty::create('__extend__parent')->setVisibility('protected'))
            ->setMethod(
                $this->generateClassMethod(
                    'getId',
                    'return $this->id;'
                )
            )
            ->setMethod(
                $this->generateClassMethod(
                    '__extend__getParent',
                    'return $this->__extend__parent;'
                )
            )
            ->setMethod(
                $this->generateClassMethod(
                    '__extend__setParent',
                    '$this->__extend__parent = $parent;return $this;',
                    array('parent')
                )
            )
            ->setMethod(
                $this->generateClassMethod(
                    '__fromArray',
                    'foreach ($values as $key => $value) {$this->{\'set\'.ucfirst($key)}($value);}',
                    array('values')
                )
            );

        $toArray = '';
        if ($fieldIds = $this->configProvider->getIds($entityName)) {
            foreach ($fieldIds as $fieldId) {
                if ($this->configProvider->getConfigById($fieldId)->is('is_extend')
                    //&& $this->configProvider->getConfigById($fieldId)->get('state') != ExtendManager::STATE_DELETED
                ) {
                    $fieldName = $fieldId->getFieldName();
                    $class
                        ->setProperty(PhpProperty::create($fieldName)->setVisibility('protected'))
                        ->setMethod(
                            $this->generateClassMethod(
                                'get' . ucfirst($fieldName),
                                'return $this->' . $fieldName . ';'
                            )
                        )
                        ->setMethod(
                            $this->generateClassMethod(
                                'set' . ucfirst($fieldName),
                                '$this->' . $fieldName . ' = $value; return $this;',
                                array('value')
                            )
                        );
                    $toArray .= '    \'' . $fieldName . '\' => $this->' . $fieldName . ',' . "\n";
                }
            }
        }

        $class->setMethod(
            $this->generateClassMethod(
                '__toArray',
                'return array(' . $toArray . "\n" . ');'
            )
        );

        $strategy = new DefaultGeneratorStrategy();

        return $strategy->generate($class);
    }

    /**
     * Generate Proxy class
     * @param      $entityName
     * @param      $className
     * @param bool $extend
     * @return string
     */
    protected function generateProxyClass($entityName, $className, $extend = true)
    {
        $this->writer = new Writer();

        $class = PhpClass::create($this->generateClassName($entityName))->setName($className);

        if ($extend) {
            $class->setParentClassName($entityName);
        }

        $class->setInterfaceNames(array('Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface'))
            ->setProperty(PhpProperty::create('__proxy__extend')->setVisibility('protected'))
            ->setMethod(
                $this->generateClassMethod(
                    '__proxy__setExtend',
                    '$this->__proxy__extend = $extend;return $this;',
                    array('extend')
                )
            )
            ->setMethod(
                $this->generateClassMethod(
                    '__proxy__getExtend',
                    'return $this->__proxy__extend;'
                )
            )
            ->setMethod(
                $this->generateClassMethod(
                    '__proxy__createFromEntity',
                    '$proxy=get_object_vars($entity);foreach ($proxy as $key=>$value){$this->$key=$value;}',
                    array('entity')
                )
            )
            ->setMethod(
                $this->generateClassMethod(
                    '__proxy__cloneToEntity',
                    '$proxy=get_object_vars($entity);foreach ($proxy as $key=>$value){$entity->$key=$this->$key;}',
                    array('entity')
                )
            );

        $toArray = '';
        if ($fieldIds = $this->configProvider->getIds($entityName)) {
            foreach ($fieldIds as $fieldId) {
                $fieldName = $fieldId->getFieldName();

                if ($this->configProvider->getConfigById($fieldId)->is('is_extend')
                    && $this->configProvider->getConfigById($fieldId)->get('state') != ExtendManager::STATE_DELETED
                ) {
                    $class->setMethod(
                        $this->generateClassMethod(
                            'set' . ucfirst($fieldName),
                            '$this->__proxy__extend->set' . ucfirst(
                                $fieldName
                            ) . '($' . $fieldName . '); return $this;',
                            array($fieldName)
                        )
                    );
                    $class->setMethod(
                        $this->generateClassMethod(
                            'get' . ucfirst($fieldName),
                            'return $this->__proxy__extend->get' . ucfirst($fieldName) . '();'
                        )
                    );

                    $toArray .= '    \'' . $fieldName . '\' => $this->__proxy__extend->get' .
                        ucfirst($fieldName) . '(),' . "\n";
                } else {
                    $toArray .= '    \'' . $fieldName . '\' => $this->get' . ucfirst($fieldName) . '(),' . "\n";
                }
            }
        }

        $class->setMethod(
            $this->generateClassMethod(
                '__proxy__toArray',
                'return array(' . $toArray . "\n" . ');'
            )
        );

        $strategy = new DefaultGeneratorStrategy();

        return $strategy->generate($class);
    }
}
