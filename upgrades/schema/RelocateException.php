<?php

namespace Pim\Upgrade\Schema;

abstract class RelocateException extends \LogicException
{
}

class OpenFileException extends RelocateException
{
    public function __construct($filePath)
    {
        parent::__construct("Unable to open the file $filePath.");
    }
}

class StoreException extends RelocateException
{
    public function __construct($fileKey)
    {
        parent::__construct("Unable to store the file $fileKey in the object storage.");
    }
}

