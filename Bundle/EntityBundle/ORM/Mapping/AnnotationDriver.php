<?php

namespace Oro\Bundle\EntityBundle\ORM\Mapping;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver as BaseAnnotationDriver;

class AnnotationDriver extends BaseAnnotationDriver
{
    public function getAllClassNames()
    {
        if ($this->classNames !== null) {
            return $this->classNames;
        }

        if (!$this->paths) {
            throw MappingException::pathRequired();
        }

        $classes       = array();
        $includedFiles = array();

        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+' . preg_quote($this->fileExtension) . '$/i',
                \RecursiveRegexIterator::GET_MATCH
            );

            foreach ($iterator as $file) {
                global $test;
                if (!isset($test) || !in_array($file[0], $test)) {
                    $sourceFile = realpath($file[0]);

                    $includedFiles[] = $sourceFile;
                }
            }
        }

        $declared = get_declared_classes();

        foreach ($declared as $className) {
            $rc         = new \ReflectionClass($className);
            $sourceFile = $rc->getFileName();
            if (in_array($sourceFile, $includedFiles) && !$this->isTransient($className)) {
                $classes[] = $className;
            }
        }

        $this->classNames = $classes;

        return $classes;
    }
}
 