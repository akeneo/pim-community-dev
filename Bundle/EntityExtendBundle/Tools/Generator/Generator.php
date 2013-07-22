<?php

namespace Oro\Bundle\EntityExtendBundle\Tools\Generator;

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
     * @param $entityName
     * @param bool $force
     */
    public function checkEntityCache($entityName, $force = false)
    {
        $extendClass = $this->generateExtendClassName($entityName);
        $proxyClass  = $this->generateProxyClassName($entityName);

        $validator   = $this->entityCacheDir. DIRECTORY_SEPARATOR . 'validator.yml';
        if (!file_exists($validator)) {
            file_put_contents(
                $validator,
                ''
            );
        }
        //$validatorYml = Yaml::parse($validator);

        if ((!class_exists($extendClass) || !class_exists($proxyClass)) || $force) {
            /** write Dynamic class */
            file_put_contents(
                $this->entityCacheDir. DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $extendClass) . '.php',
                "<?php\n\n" . $this->generateDynamicClass($entityName, $extendClass)
            );

            /** write Dynamic yml */
            file_put_contents(
                $this->entityCacheDir. DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $extendClass) . '.orm.yml',
                Yaml::dump($this->generateDynamicYml($entityName, $extendClass), 5)
            );

            /** write Proxy class */
            file_put_contents(
                $this->entityCacheDir. DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $proxyClass) . '.php',
                "<?php\n\n" . $this->generateProxyClass($entityName, $proxyClass)
            );
        }
    }

    public function generateExtendClassName($entityName)
    {
        return 'Extend\\Entity\\' . $this->backend . '\\' . $this->generateClassName($entityName);
    }

    public function generateProxyClassName($entityName)
    {
        return 'Extend\\Proxy\\' . $this->generateClassName($entityName);
    }

    protected function generateClassName($entityName)
    {
        return str_replace('\\', '', $entityName);
    }

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

        if ($fields = $this->configProvider->getConfig($entityName)->getFields()) {
            foreach ($fields as $field => $options) {
                if ($this->configProvider->getFieldConfig($entityName, $field)->is('is_extend')) {
                    $yml[$extendClass]['fields'][$field] = unserialize(
                        $this->configProvider->getFieldConfig($entityName, $field)->get('doctrine')
                    );
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
     *
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
        if ($fields = $this->configProvider->getConfig($entityName)->getFields()) {
            foreach ($fields as $field => $options) {
                if ($this->configProvider->getFieldConfig($entityName, $field)->is('is_extend')) {
                    $class
                        ->setProperty(PhpProperty::create($field)->setVisibility('protected'))
                        ->setMethod(
                            $this->generateClassMethod(
                                'get'.ucfirst($field),
                                'return $this->'.$field.';'
                            )
                        )
                        ->setMethod(
                            $this->generateClassMethod(
                                'set'.ucfirst($field),
                                '$this->'.$field.' = $value; return $this;',
                                array('value')
                            )
                        );
                    $toArray .= '    \''.$field.'\' => $this->'.$field.','."\n";
                }
            }
        }

        $class->setMethod(
            $this->generateClassMethod(
                '__toArray',
                'return array('.$toArray."\n".');'
            )
        );

        $strategy = new DefaultGeneratorStrategy();

        return $strategy->generate($class);
    }

    /**
     * Generate Proxy class
     *
     * @param $entityName
     * @param $className
     * @return $this
     */
    protected function generateProxyClass($entityName, $className)
    {
        $this->writer = new Writer();

        $class = PhpClass::create($this->generateClassName($entityName))
            ->setName($className)
            ->setParentClassName($entityName)
            ->setInterfaceNames(array('Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface'))
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
                    '$proxy=get_object_vars($entity);foreach ($proxy as $key=>$value) {$this->$key=$value;}',
                    array('entity')
                )
            )
            ->setMethod(
                $this->generateClassMethod(
                    '__proxy__cloneToEntity',
                    '$proxy=get_object_vars($entity);foreach ($proxy as $key=>$value) {$entity->$key=$this->$key;}',
                    array('entity')
                )
            );

        $toArray   = '';
        if ($fields = $this->configProvider->getConfig($entityName)->getFields()) {
            foreach ($fields as $field => $options) {
                if ($this->configProvider->getFieldConfig($entityName, $field)->is('is_extend')) {
                    $class->setMethod(
                        $this->generateClassMethod(
                            'set'.ucfirst($field),
                            '$this->__proxy__extend->set'.ucfirst($field).'($'.$field.'); return $this;',
                            array($field)
                        )
                    );
                    $class->setMethod(
                        $this->generateClassMethod(
                            'get'.ucfirst($field),
                            'return $this->__proxy__extend->get'.ucfirst($field).'();'
                        )
                    );

                    $toArray   .= '    \''.$field.'\' => $this->__proxy__extend->get'.ucfirst($field).'(),'."\n";
                } else {
                    $toArray   .= '    \''.$field.'\' => $this->get'.ucfirst($field).'(),'."\n";
                }
            }
        }

        $class->setMethod(
            $this->generateClassMethod(
                '__proxy__toArray',
                'return array('.$toArray."\n".');'
            )
        );

        $strategy = new DefaultGeneratorStrategy();

        return $strategy->generate($class);
    }
}
