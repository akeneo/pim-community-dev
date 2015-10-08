<?php
namespace Oro\Bundle\NavigationBundle\Title\TitleReader;

use Doctrine\Common\Annotations\Reader as CommonAnnotationsReader;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class AnnotationsReader extends Reader
{
    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $routes = array();

    const ANNOTATION_CLASS = 'Oro\Bundle\NavigationBundle\Annotation\TitleTemplate';

    public function __construct(KernelInterface $kernel, CommonAnnotationsReader $reader)
    {
        parent::__construct($kernel);

        $this->reader = $reader;
    }

    /**
     * Get Route/Title information from controller annotations
     *
     * @param  array $routes
     * @return array()
     */
    public function getData(array $routes)
    {
        $this->prepareRoutesArray($routes);

        $directories = $this->getScanDirectories();
        if (!$directories) {
            return array();
        }

        $files = $this->findFiles('*.php', $directories);

        foreach ($files as $index => $file) {
            if (strpos($file, 'AnnotationsReader') !== false || strpos($file, 'Annotation') !== false) {
                unset($files[$index]);
            }
        }

        return $this->findTitlesAnnotations($files);
    }

    /**
     * Get array with titles from annotations
     *
     * @param array $files
     *
     * @return array()
     */
    private function findTitlesAnnotations(array $files)
    {
        $titles = array();

        foreach ($files as $file) {
            $className = $this->getClassName($file);
            if ($className) {
                $reflection = new \ReflectionClass($className);

                //read annotations from methods
                foreach ($reflection->getMethods() as $reflectionMethod) {
                    $title = $this->reader->getMethodAnnotation($reflectionMethod, self::ANNOTATION_CLASS);
                    if (is_object($title)) {
                        $titles[$this->getDefaultRouteName($reflection, $reflectionMethod)] = $title->getTitleTemplate(
                        );
                    }
                }
            }
        }

        return $titles;
    }

    /**
     * Prepare routes array for using in this reader
     *
     * @param array $routes
     */
    private function prepareRoutesArray(array $routes)
    {
        foreach ($routes as $name => $route) {
            /** @var $route \Symfony\Component\Routing\Route */
            $this->routes[$route->getDefault('_controller')] = $name;
        }
    }

    /**
     * Gets the default route name for a class method.
     *
     * @param \ReflectionClass $class
     * @param \ReflectionMethod $method
     *
     * @throws \RuntimeException if route doesn't exist
     * @return string
     */
    private function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        $key = $class->getName() . '::' . $method->getName();

        if (array_key_exists($key, $this->routes)) {
            return $this->routes[$key];
        }

        throw new \RuntimeException(sprintf('Route doesn\'t exist for "%s".', $key));
    }

    /**
     * Only supports one namespaced class per file
     *
     * @throws \RuntimeException if the class name cannot be extracted
     *
     * @param string $filename
     *
     * @return string the fully qualified class name
     */
    private function getClassName($filename)
    {
        $src = file_get_contents($filename);

        if (!preg_match('#' . str_replace("\\", "\\\\", self::ANNOTATION_CLASS) . '#', $src)) {
            return null;
        }

        if (!preg_match('/\bnamespace\s+([^;]+);/s', $src, $match)) {
            throw new \RuntimeException(sprintf('Namespace could not be determined for file "%s".', $filename));
        }
        $namespace = $match[1];

        if (!preg_match('/\bclass\s+([^\s]+)\s+(?:extends|implements|{)/s', $src, $match)) {
            throw new \RuntimeException(sprintf('Could not extract class name from file "%s".', $filename));
        }

        return $namespace . '\\' . $match[1];
    }

    /**
     * @param $filePattern
     * @param array $dirs
     * @return array
     */
    private function findFiles($filePattern, array $dirs)
    {
        $finder = new Finder();
        $finder
            ->files()
            ->name($filePattern)
            ->in($dirs)
            ->ignoreVCS(true);

        $result = array();
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $result[] = $file->getRealPath();
        }

        return $result;
    }
}
