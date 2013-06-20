<?php

namespace Oro\Bundle\EntityExtendBundle\Tools\Generator;

use CG\Core\DefaultGeneratorStrategy;
use CG\Generator\PhpClass;
use CG\Generator\PhpMethod;
use CG\Generator\PhpParameter;
use CG\Generator\PhpProperty;
use CG\Generator\Writer;
use Oro\Bundle\EntityExtendBundle\Config\ExtendConfigProvider;

class Generator
{
    /**
     * @var string
     */
    protected $mode;

    /**
     * @var ExtendConfigProvider
     */
    protected $configProvider;

    /**
     * @var Writer
     */
    protected $writer = null;



    /**
     * @param ExtendConfigProvider $configProvider
     * @param                      $mode
     */
    public function __construct(ExtendConfigProvider $configProvider, $mode)
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

    protected function generateYaml(){}


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
            ->setProperty(PhpProperty::create('parent')->setVisibility('protected'));

        $this->writer->reset();
        $class->setMethod(PhpMethod::create('getId')->setBody(
            $this->writer
                ->write('return $this->id;')
                ->getContent()
            )
        );

        $this->writer->reset();
        $class->setMethod(PhpMethod::create('getParent')->setBody(
            $this->writer
                ->write('return $this->parent;')
                ->getContent()
            )
        );

        $this->writer->reset();
        $class->setMethod(PhpMethod::create('setParent')->setBody(
            $this->writer
                ->writeln('$this->parent = $parent;')
                ->writeln('return $this;')
                ->getContent()
            )
            ->addParameter(PhpParameter::create('parent'))
        );

        $this->writer->reset();
        $class->setMethod(PhpMethod::create('set')->setBody(
            $this->writer
                ->writeln('return $this->{\'set\' . ucfirst($key)}($value);')
                ->getContent()
            )
            ->addParameter(PhpParameter::create('key'))
            ->addParameter(PhpParameter::create('value'))
        );

        $this->writer->reset();
        $class->setMethod(PhpMethod::create('get')->setBody(
            $this->writer
                ->writeln('return $this->{\'get\' . ucfirst($key)}();')
                ->getContent()
            )
            ->addParameter(PhpParameter::create('key'))
        );

        $this->writer->reset();
        $class->setMethod(PhpMethod::create('__fromArray')->setBody(
            $this->writer
                ->writeln('foreach($values as $key => $value) {')
                ->writeln('    $this->set($key, $value);')
                ->writeln('}')
                ->getContent()
            )
            ->addParameter(PhpParameter::create('values'))
        );

        $fields = $this->configProvider->getConfig($entityName)->getFields();
        $toArray = '';
        if($fields) {
            foreach ($fields as $field => $options) {
                if (1!=1) {
                    $toArray .= '    \''.$field.'\' => $this->'.$field.','."\n";
                }
            }
        }

        $this->writer->reset();
        $class->setMethod(PhpMethod::create('__toArray')->setBody(
            $this->writer
                ->writeln('return array(')
                ->writeln($toArray)
                ->writeln(');')
                ->getContent()
            )
        );

        $strategy = new DefaultGeneratorStrategy();

        return $strategy->generate($class);
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
}