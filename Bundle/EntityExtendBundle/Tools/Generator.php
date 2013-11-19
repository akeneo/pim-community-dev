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

class Generator
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var string
     */
    protected $entityCacheDir;

    /**
     * @var Writer
     */
    protected $writer = null;

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
        $this->entityCacheDir = ExtendClassLoadingUtils::getEntityCacheDir($cacheDir);
    }

    /**
     * Generates extended entities
     *
     * @param array $config
     */
    public function generate(array $config)
    {
        $aliases = array();
        foreach ($config as $item) {
            $this->generateYaml($item);
            $this->generateClass($item);
            if ($item['type'] == 'Extend') {
                $aliases[$item['entity']] = $item['parent'];
            }
        }
        file_put_contents(ExtendClassLoadingUtils::getAliasesPath($this->cacheDir), Yaml::dump($aliases));
    }

    protected function generateYaml($item)
    {
        $classNameArray = explode('\\', $item['entity']);
        file_put_contents(
            $this->entityCacheDir . '/' . array_pop($classNameArray) . '.orm.yml',
            Yaml::dump($item['doctrine'], 5)
        );
    }

    protected function generateClass($item)
    {
        $this->writer = new Writer();

        $class = PhpClass::create($item['entity']);

        if ($item['type'] == 'Extend') {
            if (isset($item['inherit'])) {
                $class->setParentClassName($item['inherit']);
            }
        } else {
            $class->setProperty(PhpProperty::create('id')->setVisibility('protected'));
            $class->setMethod($this->generateClassMethod('getId', 'return $this->id;'));

            /**
             * TODO
             * custom entity instance as manyToOne relation
             * find the way to show it on view
             * we should mark some field as title
             */
            $toString = array();
            foreach ($item['property'] as $propKey => $propValue) {
                if ($item['doctrine'][$item['entity']]['fields'][$propKey]['type'] == 'string') {
                    $toString[] = '$this->get' . ucfirst(Inflector::camelize($propValue)) . '()';
                }
            }

            $toStringBody = 'return (string) $this->getId();';
            if (count($toString) > 0) {
                $toStringBody = 'return (string)' . implode(' . ', $toString) . ';';
            }
            $class->setMethod($this->generateClassMethod('__toString', $toStringBody));
        }

        $class->setInterfaceNames(array('Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface'));

        $this->generateClassMethods($item, $class);

        $classArray = explode('\\', $item['entity']);
        $className  = array_pop($classArray);

        $filePath = $this->entityCacheDir . '/' . $className . '.php';
        $strategy = new DefaultGeneratorStrategy();
        file_put_contents($filePath, "<?php\n\n" . $strategy->generate($class));
    }

    /**
     * @param $config
     * @param $class
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function generateClassMethods($config, &$class)
    {
        foreach ($config['property'] as $property => $method) {
            $class
                ->setProperty(PhpProperty::create($property)->setVisibility('protected'))
                ->setMethod(
                    $this->generateClassMethod(
                        'get' . ucfirst(Inflector::camelize($method)),
                        'return $this->' . $property . ';'
                    )
                )
                ->setMethod(
                    $this->generateClassMethod(
                        'set' . ucfirst(Inflector::camelize($method)),
                        '$this->' . $property . ' = $value; return $this;',
                        array('value')
                    )
                );
        }

        foreach ($config['relation'] as $relation => $method) {
            $class
                ->setProperty(PhpProperty::create($relation)->setVisibility('protected'))
                ->setMethod(
                    $this->generateClassMethod(
                        'get' . ucfirst(Inflector::camelize($method)),
                        'return $this->' . $relation . ';'
                    )
                )
                ->setMethod(
                    $this->generateClassMethod(
                        'set' . ucfirst(Inflector::camelize($method)),
                        '$this->' . $relation . ' = $value; return $this;',
                        array('value')
                    )
                );
        }

        foreach ($config['default'] as $default => $method) {
            $class
                ->setProperty(PhpProperty::create($default)->setVisibility('protected'))
                ->setMethod(
                    $this->generateClassMethod(
                        'get' . ucfirst(Inflector::camelize($method)),
                        'return $this->' . $default . ';'
                    )
                )
                ->setMethod(
                    $this->generateClassMethod(
                        'set' . ucfirst(Inflector::camelize($method)),
                        '$this->' . $default . ' = $value; return $this;',
                        array('value')
                    )
                );
        }

        foreach ($config['addremove'] as $addremove => $method) {
            $class
                ->setMethod(
                    $this->generateClassMethod(
                        'add' . ucfirst(Inflector::camelize($method['self'])),
                        'if (!$this->' . $addremove . ') {
                            $this->' . $addremove . ' = new \Doctrine\Common\Collections\ArrayCollection();
                        }
                        if (!$this->' . $addremove . '->contains($value)) {
                            $this->' . $addremove . '->add($value);
                            $value->' . ($method['is_target_addremove'] ? 'add' : 'set')
                        . ucfirst(Inflector::camelize($method['target'])) .'($this);
                        }',
                        array('value')
                    )
                )
                ->setMethod(
                    $this->generateClassMethod(
                        'remove' . ucfirst(Inflector::camelize($method['self'])),
                        'if ($this->' . $addremove . ' && $this->' . $addremove . '->contains($value)) {
                            $this->' . $addremove . '->removeElement($value);
                            $value->'. ($method['is_target_addremove'] ? 'remove' : 'set')
                        . ucfirst(Inflector::camelize($method['target']))
                        .'(' . ($method['is_target_addremove'] ? '$this' : 'null') . ');
                        }',
                        array('value')
                    )
                );
        }
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
}
