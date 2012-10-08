<?php
namespace Strixos\DataFlowBundle\Model\Extract;

use Strixos\DataFlowBundle\Entity\Step;

/**
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FileUnzip extends Step
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
    public function process($pathArchive, $pathFile)
    {
    	if (!file_exists($pathArchive)) {
            throw new UnzipException ('unzip.archive_file.unknown');
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