<?php

namespace Oro\Bundle\SecurityBundle\Annotation\Loader;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationStorage;
use Symfony\Component\Finder\Finder;

class AclAnnotationLoader extends AbstractLoader implements AclAnnotationLoaderInterface
{
    const ANNOTATION_CLASS = Acl::class;
    const ANCESTOR_CLASS = AclAncestor::class;

    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var string[]
     */
    private $subDirs;

    /**
     * Constructor
     *
     * @param string[] $bundles A list of loaded bundles
     * @param string[] $subDirs A list of sub directories (related to a bundle directory)
     *                          where classes with ACL annotations may be located
     * @param AnnotationReader $reader
     */
    public function __construct($bundles, array $subDirs, AnnotationReader $reader)
    {
        parent::__construct($bundles);
        $this->reader = $reader;
        $this->subDirs = $subDirs;
    }

    /**
     * Loads ACL annotations from PHP files
     *
     * @param AclAnnotationStorage $storage
     */
    public function load(AclAnnotationStorage $storage)
    {
        if (!empty($this->subDirs)) {
            $directories = [];
            foreach ($this->bundleDirectories as $bundleDir) {
                foreach ($this->subDirs as $subDir) {
                    $dir = $bundleDir . DIRECTORY_SEPARATOR . $subDir;
                    if (is_dir($dir)) {
                        $directories[] = $dir;
                    }
                }
            }
        } else {
            $directories = $this->bundleDirectories;
        }

        $files = $this->findFiles('*.php', $directories);

        foreach ($files as $file) {
            $className = $this->getClassName($file);
            if ($className !== null) {
                $reflection = $this->getReflectionClass($className);
                // read annotations from class
                $annotation = $this->reader->getClassAnnotation($reflection, self::ANNOTATION_CLASS);
                if ($annotation) {
                    $storage->add($annotation, $reflection->getName());
                } else {
                    $ancestor = $this->reader->getClassAnnotation($reflection, self::ANCESTOR_CLASS);
                    if ($ancestor) {
                        $storage->addAncestor($ancestor, $reflection->getName());
                    }
                }
                // read annotations from methods
                foreach ($reflection->getMethods() as $reflectionMethod) {
                    $annotation = $this->reader->getMethodAnnotation($reflectionMethod, self::ANNOTATION_CLASS);
                    if ($annotation) {
                        $storage->add($annotation, $reflection->getName(), $reflectionMethod->getName());
                    } else {
                        $ancestor = $this->reader->getMethodAnnotation($reflectionMethod, self::ANCESTOR_CLASS);
                        if ($ancestor) {
                            $storage->addAncestor($ancestor, $reflection->getName(), $reflectionMethod->getName());
                        }
                    }
                }
            }
        }
    }

    /**
     * Gets a class name from the given file.
     *
     * Restrictions:
     *      - only one class must be declared in a file
     *      - a namespace must be declared in a file
     *
     * @param  string      $fileName
     * @return null|string the fully qualified class name or null if the class name cannot be extracted
     */
    protected function getClassName($fileName)
    {
        $src = $this->getFileContent($fileName);
        if (!preg_match('#' . str_replace("\\", "\\\\", self::ANNOTATION_CLASS) . '#', $src)) {
            return null;
        }

        if (!preg_match('/\bnamespace\s+([^;]+);/s', $src, $match)) {
            return null;
        }
        $namespace = $match[1];

        if (!preg_match('/\bclass\s+([^\s]+)\s+(?:extends|implements|{)/s', $src, $match)) {
            return null;
        }

        return $namespace . '\\' . $match[1];
    }

    /**
     * Creates ReflectionClass object
     *
     * @param  string           $className
     * @return \ReflectionClass
     */
    protected function getReflectionClass($className)
    {
        return new \ReflectionClass($className);
    }

    /**
     * Reads the given file into a string
     *
     * @param  string $fileName
     * @return string
     */
    protected function getFileContent($fileName)
    {
        return file_get_contents($fileName);
    }

    /**
     * @param $filePattern
     * @param  array $dirs
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

        $result = [];
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $result[] = $file->getRealPath();
        }

        return $result;
    }
}
