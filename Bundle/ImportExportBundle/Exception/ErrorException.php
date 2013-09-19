<?php

namespace Oro\Bundle\ImportExportBundle\Exception;

class ErrorException extends \ErrorException implements Exception
{
    const CRITICAL = 1;
    const WARNING  = 2;
    const NOTICE   = 3;
}
