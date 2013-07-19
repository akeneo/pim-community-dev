<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileExistsException extends \Exception
{
    public function __construct($filename)
    {
        parent::__construct(sprintf('File "%s" already exists.', $filename));
    }
}
