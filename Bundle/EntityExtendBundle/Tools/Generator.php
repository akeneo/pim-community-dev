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
    protected $entityDir;

    /**
     * @var Writer
     */
    protected $writer = null;

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir  = $cacheDir;
        $this->entityDir = $cacheDir . '/Extend/Entity';
    }

    public function generate()
    {
        if (!file_exists($this->cacheDir . '/entity_config.yml')) {
            return;
        }

        $data = Yaml::parse(file_get_contents($this->cacheDir . '/entity_config.yml'));

        foreach ($data as $item) {
            $this->generateYaml($item);
            $this->generateClass($item);
        }

        $this->generateAlias($data);
    }

    public function generateYaml($item)
    {
        $classNameArray = explode('\\', $item['entity']);
        file_put_contents(
            $this->entityDir . '/' . array_pop($classNameArray) . '.orm.yml',
            Yaml::dump($item['doctrine'], 5)
        );
    }

    public function generateAlias($data)
    {
        $aliases = array();

        foreach ($data as $item) {
            if ($item['type'] == 'Extend') {
                $aliases[$item['entity']] = $item['parent'];
            }
        }

        file_put_contents($this->entityDir . '/alias.yml', Yaml::dump($aliases));
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
             * we should mark some field as
             */
            $toString = array();
            foreach ($item['property'] as $propKey => $propValue) {
                if ($item['doctrine'][$item['entity']]['fields'][$propKey]['type'] == 'string') {
                    $toString[] = '$this->get' . ucfirst(Inflector::camelize($propValue)) . '()';
                }
            }

            $toStringBody = '(string) return $this->getId();';
            if (count($toString) > 0) {
                $toStringBody = 'return (string)' . implode(' . ', $toString) . ';';
            }
            $class->setMethod($this->generateClassMethod('__toString', $toStringBody));
        }

        $class->setInterfaceNames(array('Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface'));

        $this->generateClassMethods($item['property'], $class);

        $classArray = explode('\\', $item['entity']);
        $className  = array_pop($classArray);

        $filePath = $this->entityDir . '/' . $className . '.php';
        $strategy = new DefaultGeneratorStrategy();
        file_put_contents($filePath, "<?php\n\n" . $strategy->generate($class));
    }

    /**
     * @param $properties
     * @param $class
     */
    protected function generateClassMethods($properties, &$class)
    {
        foreach ($properties as $property => $method) {
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
