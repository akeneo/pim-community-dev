<?php

namespace Oro\Bundle\EntityExtendBundle\Tools\Generator;

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
    protected $mode;

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
     * @param                      $mode
     */
    public function __construct(ConfigProvider $configProvider, $mode)
    {
        $this->mode           = $mode;
        $this->configProvider = $configProvider;
    }

    /**
     * @param $entityName
     */
    public function checkEntityCache($entityName)
    {
        $extendClass = $this->generateExtendClassName($entityName);

//        var_dump($extendClass);
//        print_r(array_keys(
//            $this->configProvider->getConfig($entityName)->getFields()
//        ));
//        var_dump(
//            $this->configProvider->getConfig($entityName)->getValues()
//        );

        if (!class_exists($extendClass)) {

            var_dump($this->mode);
            var_dump($entityName);
            var_dump($extendClass);

            echo '<pre>', print_r($this->generateDynamicClass($entityName, $extendClass), 1), '</pre>';

            //$this->path = $this->getContainer()->getParameter('kernel.root_dir') . '/entities/Extend/Entity/';
            //file_put_contents($this->getPath() . $this->getFilename($emd) . '.php', "<?php\n\n" . $strategy->generate($this->class));
            //file_put_contents($this->getPath() . $this->getFilename($emd) . '.orm.yml', Yaml::dump($this->classYml, 5));

        }
        die('generator');
    }

    public function generateExtendClassName($entityName)
    {
        return 'Extend\\Entity\\' . $this->mode . '\\' . $this->generateClassName($entityName);
    }

    public function generateProxyClassName($entityName)
    {
        return 'Extend\\Entity\\Proxy\\' . $this->generateClassName($entityName);
    }

    protected function generateClassName($entityName)
    {
        return str_replace('\\', '', $entityName);
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
     * Generate Dynamic class
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
            ->setProperty(PhpProperty::create('parent')->setVisibility('protected'))
            ->setMethod($this->generateClassMethod(
                'getId',
                'return $this->id;'
            ))
            ->setMethod($this->generateClassMethod(
                'getParent',
                'return $this->parent;'
            ))
            ->setMethod($this->generateClassMethod(
                'setParent',
                '$this->parent = $parent;return $this;', array('parent')
            ))
            ->setMethod($this->generateClassMethod(
                'set',
                'return $this->{\'set\'.ucfirst($key)}($value);',
                array('key', 'value')
            ))
            ->setMethod($this->generateClassMethod(
                'get',
                'return $this->{\'get\'.ucfirst($key)}();',
                array('key')
            ))
            ->setMethod($this->generateClassMethod(
                '__fromArray',
                'foreach($values as $key => $value){$this->set($key, $value);}',
                array('values')
            ));

        $fields = $this->configProvider->getConfig($entityName)->getFields();
        $toArray = '';
        if($fields) {
            foreach ($fields as $field => $options) {
                if ($this->configProvider->getFieldConfig($entityName, $field)->is('is_extend')) {
                    $toArray .= "\n".'    \''.$field.'\' => $this->'.$field.',';
                }
            }
        }

        $class->setMethod($this->generateClassMethod(
            '__toArray',
            'return array('.$toArray."\n".');'
        ));

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
            ->setInterfaceNames(array('Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface'))
            ->setProperty(PhpProperty::create('id')->setVisibility('protected'))
            ->setProperty(PhpProperty::create('parent')->setVisibility('protected'));

        $strategy = new DefaultGeneratorStrategy();

        return $strategy->generate($class);
    }
}