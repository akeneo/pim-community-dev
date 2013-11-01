<?php

namespace Oro\Bundle\SoapBundle\ServiceDefinition\Loader;

use Symfony\Component\Config\FileLocator;

use BeSimple\SoapBundle\ServiceDefinition\Loader\AnnotationFileLoader;
use BeSimple\SoapBundle\ServiceDefinition\Loader\AnnotationClassLoader;

class OroSoapLoader extends AnnotationFileLoader
{
    protected $classes;

    /**
     *
     * @param FileLocator           $locator
     * @param AnnotationClassLoader $loader  An AnnotationClassLoader instance
     * @param string|array          $paths   A path or an array of paths where to look for resources
     */
    public function __construct(FileLocator $locator, AnnotationClassLoader $loader, $paths = array())
    {
        parent::__construct($locator, $loader, $paths);

        $this->classes = $paths;
    }

    /**
     * Loads a ServiceDefinition from annotations from a yml file.
     *
     * @param string $file
     * @param string $type
     *
     * @return \BeSimple\SoapBundle\ServiceDefinition\Definition
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function load($file, $type = null)
    {
        $definition = null;

        foreach ($this->classes as $class) {
            $newDefinition = $this->loader->load($class);

            if (!$definition) {
                $definition = clone $newDefinition;
            } else {
                foreach ($newDefinition->getMethods() as $method) {
                    $definition->addMethod(clone $method);
                }
            }
        }

        return $definition;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed $resource
     * @param null  $type
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return 'oro_soap' === $type;
    }
}
