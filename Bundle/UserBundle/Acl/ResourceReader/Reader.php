<?php
namespace Oro\Bundle\UserBundle\Acl\ResourceReader;

use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\Common\Annotations\Reader as AnnotationReader;

use JMS\DiExtraBundle\Finder\PatternFinder;

class Reader
{
    const ACL_CLASS = 'Oro\Bundle\UserBundle\Annotation\Acl';

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    public function __construct(KernelInterface $kernel, AnnotationReader $reader)
    {
        $this->kernel = $kernel;
        $this->reader = $reader;
    }

    /**
     * Return array tree with resources
     *
     * @param string $directory
     *
     * @return \Oro\Bundle\UserBundle\Annotation\Acl[]
     */
    public function getResources($directory = '')
    {
        if (!$directory){
            $directories = $this->getScanDirectories();
            if (!$directories) {
                return array();
            }
        } else {
            $directories[] = $directory;
        }

        $inTest = false;
        foreach ($directories as $directory) {
            if (strpos($directory, 'Unit') !== false) {
                $inTest = true;
            }
        }

        $finder = new PatternFinder(self::ACL_CLASS, '*.php');
        $files = $finder->findFiles($directories);

        foreach ($files as $index => $file) {
            if (strpos($file, 'Annotation') !== false
                || strpos($file, 'ResourceReader') !== false
                || (!$inTest && strpos($file, 'Test') !== false)
            ) {
                unset($files[$index]);
            }
        }

        return $this->findResources($files);
    }

    /**
     * Get array with resources from annotations
     *
     * @param array $files
     *
     * @return \Oro\Bundle\UserBundle\Annotation\Acl[]
     */
    private function findResources(array $files)
    {
        $aclResources = array();
        foreach ($files as $file) {
            $className = $this->getClassName($file);
            $reflection = new \ReflectionClass($className);
            //read annotations from class definition
            $classAcl = $this->reader->getClassAnnotation($reflection, self::ACL_CLASS);
            if (is_object($classAcl)) {
                $aclResources[$classAcl->getId()] = $classAcl;
            }
            //read annotations from methods
            foreach ($reflection->getMethods() as $reflectionMethod) {
                $acl = $this->reader->getMethodAnnotation($reflectionMethod, self::ACL_CLASS);
                if (is_object($acl)) {
                    $acl->setClass($className);
                    $acl->setMethod($reflectionMethod->getName());
                    $aclResources[$acl->getId()] = $acl;
                }
            }
        }

        return $aclResources;
    }

    /**
     * get dir array of bundles
     *
     * @return array
     */
    private function getScanDirectories()
    {
        $directories = false;
        $bundles = $this->kernel->getBundles();

        foreach ($bundles as $bundle) {
            $directories[] = $bundle->getPath();
        }

        return $directories;
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

        if (!preg_match('/\bnamespace\s+([^;]+);/s', $src, $match)) {
            throw new \RuntimeException(sprintf('Namespace could not be determined for file "%s".', $filename));
        }
        $namespace = $match[1];

        if (!preg_match('/\bclass\s+([^\s]+)\s+(?:extends|implements|{)/s', $src, $match)) {
            throw new \RuntimeException(sprintf('Could not extract class name from file "%s".', $filename));
        }

        return $namespace . '\\' . $match[1];
    }
}
