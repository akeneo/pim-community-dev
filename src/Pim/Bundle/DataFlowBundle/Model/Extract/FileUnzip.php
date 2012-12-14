<?php
namespace Pim\Bundle\DataFlowBundle\Model\Extract;

/**
 * Unzip a file with gzip unpacker
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
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
     * @param string  $pathArchive The file to unpack
     * @param string  $pathFile    The file unpacked
     * @param boolean $forced      Unpack file even if already exists
     *
     * @throws Exception
     */
    public function process($pathArchive, $pathFile, $forced = true)
    {
        if ($forced || !file_exists($pathFile)) {
            if (!file_exists($pathArchive)) {
                throw new \Exception('unzip.archive_file.unknown');
            }
            $gzip = gzopen($pathArchive, 'rb');
            // delete destination file if already exists
            if (file_exists($pathFile)) {
                unlink($pathFile);
            }
            $fileToWrite = fopen($pathFile, 'w+');
            while (!gzeof($gzip)) {
                $buffer = gzgets($gzip, 100000); // TODO hardcoded lenght
                fputs($fileToWrite, $buffer);
            }
            gzclose($gzip);
            fclose($fileToWrite);
        }
    }
}
