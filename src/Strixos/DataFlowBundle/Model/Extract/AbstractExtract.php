<?php
namespace Strixos\DataFlowBundle\Model\Extract;

/**
 * aims to define a generic extract class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * @abstract
 *
 */
abstract class AbstractExtract
{
    /**
     * constructor call initialize method
     */
    public function __construct()
    {
    	ini_set('max_execution_time', 0);
    	ini_set('max_input_time', -1);
        $this->initialize();
    }
    
    /**
     * initialize method to redefine object instanciation
     */
    public function initialize() { }
    
    /**
     * execute extract process
     * @abstract
     */
    //abstract public function process();
    
    /**
     * Download file in defined path
     * @param string $url
     * @param string $path
     */
    public function download($url, $path)
    {
        // get config for user login and password
    
        $downloader = new FileHttpDownload();
        $downloader->process($url, $path);
    }
    
    /**
     * Unzip file
     * @param string $archivePath
     * @param string $filePath
     *
     * TODO : set args for unzip password for example
     */
    public function unzip($archivePath, $filePath)
    {
        $unzipper = new FileUnzip();
        $unzipper->process($archivePath, $filePath);
    }
}