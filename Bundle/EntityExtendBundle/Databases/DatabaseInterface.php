<?php

namespace Oro\Bundle\EntityExtendBundle\Databases;

interface DatabaseInterface
{
    /**
     * Create a database dump
     *
     * @param $destinationFile
     * @return boolean
     */
    public function dump($destinationFile);

    /**
     * Restore a database dump
     *
     * @param $sourceFile
     * @return boolean
     */
    public function restore($sourceFile);

    /**
     * Return the file extension of a dump file (sql, ...)
     *
     * @return mixed
     */
    public function getFileExtension();
}
