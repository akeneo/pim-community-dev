<?php
namespace Pim\Bundle\DataFlowBundle\Model\Extract;

/**
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FileUnzip
{

    /**
     * Aims to unzip an archive to file
     *
     * TODO: add options + deal with exceptions
     *
     * @param string $pathArchive
     * @param string $pathFile
     * @param string $login
     * @param string $password
     * @throws Exception
     */
    public function process($pathArchive, $pathFile, $forced = true)
    {
        if ($forced || !file_exists($pathFile)) {
            if (!file_exists($pathArchive)) {
                throw new \Exception ('unzip.archive_file.unknown');
            }
            $gz = gzopen($pathArchive, 'rb');
            // delete destination file if already exists
            if (file_exists($pathFile)) {
                unlink($pathFile);
            }
            $fileToWrite = fopen($pathFile, 'w+');
            while (!gzeof($gz)) {
                $buffer = gzgets($gz, 100000); // TODO hardcoded lenght
                fputs($fileToWrite, $buffer);
            }
            gzclose($gz);
            fclose($fileToWrite);
        }
    }
}