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
use Oro\Bundle\EntityExtendBundle\Exception\RuntimeException;
use Oro\Bundle\EntityExtendBundle\Extend\ExtendManager;

class Generator
{
    const ENTITY = 'Extend\\Entity\\';
    const PREFIX = 'field_';

    /**
     * @var string
     */
    protected $entityCacheDir;

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
     * @param string $entityCacheDir
     */
    public function __construct(OroEntityManager $em, $entityCacheDir)
    {
        $this->entityCacheDir = $entityCacheDir;
        $this->em             = $em;
        $this->extendManager  = $em->getExtendManager();
    }

    public function initBase()
    {
        $aliases = array();
        $configProvider = $this->extendManager->getConfigProvider();

        /** @var ClassMetadataInfo $metadata */
        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $metadata) {
            $originalClassName       = $metadata->getName();
            $originalParentClassName = get_parent_class($originalClassName);

            $parentClassArray = explode('\\', $originalParentClassName);
            $classArray       = explode('\\', $originalClassName);

            $parentClassName = array_pop($parentClassArray);
            $className       = array_pop($classArray);

            if ($parentClassName == 'Extend' . $className) {
                if (!$configProvider->hasConfig($originalClassName)) {
                    throw new RuntimeException(sprintf('Class "%s" should be configurable.', $originalClassName));
                }

                $config = $configProvider->getConfig($originalClassName);

                if ($inheritedClass = get_parent_class($originalParentClassName)) {
                    $config->set('inheritance', $inheritedClass);
                }

                $config->set('is_extend', true);
                $config->set('extend_class', self::ENTITY . $parentClassName);

                $configProvider->persist($config);

                $aliases[self::ENTITY . $parentClassName] = $originalParentClassName;

                $this->writer = new Writer();
                $class = PhpClass::create(self::ENTITY . $parentClassName);

                if ($inheritedClass) {
                    $class->setParentClassName($inheritedClass);
                }

                $strategy = new DefaultGeneratorStrategy();

                $filePath = $this->entityCacheDir . '/Extend/Entity/'. $parentClassName. '.php';
                file_put_contents($filePath, "<?php\n\n" . $strategy->generate($class));
            }
        }

        $configProvider->flush();

        file_put_contents($this->entityCacheDir . '/Extend/Entity/alias.yml', Yaml::dump($aliases));
    }

    public function generate($className)
    {
        if (strpos($className, self::ENTITY) === false) {
            if ($this->em->isExtendEntity($className)) {
                $this->generateExtendClass($className);
                $this->generateExtendYaml($className);
            }
        } else {
            $this->generateCustomClass($className);
            $this->generateCustomYaml($className);
        }

        $this->generateValidation($className);
    }

    protected function generateExtendClass($className)
    {
        $this->writer = new Writer();

        $config = $this->extendManager->getConfigProvider()->getConfig($className);
        $extendClass = $config->get('extend_class');
        $parentClass = $config->get('inheritance');

        $class = PhpClass::create($extendClass)
            ->setParentClassName($parentClass)
            ->setInterfaceNames(array('Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface'));

        $this->generateClassMethods($className, $class);

        $filePath = $this->entityCacheDir . '/' . str_replace('\\', '/', $extendClass) . '.php';

        $strategy = new DefaultGeneratorStrategy();

        file_put_contents($filePath, "<?php\n\n" . $strategy->generate($class));
    }

    /**
     * @param $className
     */
    protected function generateExtendYaml($className)
    {
        $config = $this->extendManager->getConfigProvider()->getConfig($className);
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

        $ymlPathDist = $this->entityCacheDir . '/' . str_replace('\\', '/', $extendClass) . '.orm.yml';

        $this->generateYamlMethods($extendClass, $className, $yml);

        file_put_contents($ymlPathDist, Yaml::dump($yml, 5));
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
            $this->entityCacheDir . '/Extend/Entity/' . array_pop($classNameArray) . '.php',
            "<?php\n\n" . $strategy->generate($class)
        );
    }

    /**
     * @param $className
     */
    protected function generateCustomYaml($className)
    {
        $yml = array(
            $className => array(
                'type'   => 'entity',
                'table'  => 'oro_extend_' . strtolower(str_replace('\\', '', $className)),
                'fields' => array(
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

        $this->generateYamlMethods($className, $className, $yml);

        $classNameArray = explode('\\', $className);
        file_put_contents(
            $this->entityCacheDir . '/Extend/Entity/' . array_pop($classNameArray) . '.orm.yml',
            Yaml::dump($yml, 5)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
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
            $this->entityCacheDir . '/Extend/Validator/' . str_replace('\\', '.', $className) . '.yml',
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

                    if ($fieldConfig->get('is_indexable')
                        && $fieldConfig->get('state') != ExtendManager::STATE_DELETED
                        && !in_array($fieldConfig->getId()->getFieldType(), array('text'))
                    ) {
                        $yml[$extendClassName]['indexes'][$fieldName . '_index']['columns'] = array($fieldName);
                    }
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
                    $fieldName = self::PREFIX . $fieldId->getFieldName();
                    $class
                        ->setProperty(PhpProperty::create($fieldName)->setVisibility('protected'))
                        ->setMethod(
                            $this->generateClassMethod(
                                'get' . ucfirst(Inflector::camelize($fieldName)),
                                'return $this->' . $fieldName . ';'
                            )
                        )
                        ->setMethod(
                            $this->generateClassMethod(
                                'set' . ucfirst(Inflector::camelize($fieldName)),
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
