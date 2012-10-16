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
class FileHttpDownload extends Step
{

    /**
     * Process downloading a file with curl, if login and password are provided
     * use them for basic authentication
     *
     * TODO : replace login / password by option array
     * TODO : throw exception on fopen function
     *
     * @param string $url
     * @param string $path
     * @param string $login
     * @param string $password
     * @throws Exception
     */
    public function process($url, $path, $login = null, $password = null, $forced = true)
    {
        if ($forced || !file_exists($path)) {
            // use curl to download file (use writable stream to avoid to load
            // whole file in memory)
            $fp = fopen($path, 'w+');
            $ch = curl_init($url);
            if (!$ch) {
                throw new \Exception('Curl not initialized');
            }
            
            if ($login and $password) {
                curl_setopt($ch, CURLOPT_USERPWD, $login.':'.$password);
            }
            
            // Fix SSL certificate problem
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $data = curl_exec($ch);
            if ($data === false) {
                throw new \Exception('Curl Error : '.curl_error($ch));
            }
            curl_close($ch);
            fclose($fp);
        }
    }
}