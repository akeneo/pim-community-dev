<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

/**
 * Generate the path of files to export.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FileExporterPathGeneratorInterface
{
    /**
     * @param mixed $object  The object from which the file should be retrieved
     * @param array $options
     *
     * @return string the export path of the file
     */
    public function generate($object, array $options = []);
}
