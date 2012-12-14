<?php
namespace Pim\Bundle\DataFlowBundle\Model\Extract;

/**
 * Download a file in HTTP protocol with curl library
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FileHttpDownload
{

    /**
     * Process downloading a file with curl, if login and password are provided
     * use them for basic authentication
     *
     * TODO : replace login / password by option array
     * TODO : throw exception on fopen function
     *
     * @param string  $url      download url
     * @param string  $path     local path
     * @param string  $login    login
     * @param string  $password password
     * @param boolean $forced   force download
     *
     * @throws \Exception
     */
    public function process($url, $path, $login = null, $password = null, $forced = true)
    {
        if ($forced || !file_exists($path)) {
            // use curl to download file (use writable stream to avoid to load
            // whole file in memory)
            $handle = fopen($path, 'w+');
            $curl = curl_init($url);
            if (!$curl) {
                throw new \Exception('Curl not initialized');
            }

            if ($login and $password) {
                curl_setopt($curl, CURLOPT_USERPWD, $login.':'.$password);
            }

            // Fix SSL certificate problem
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_FILE, $handle);
            $data = curl_exec($curl);

            // Deal with curl exception
            if ($data === false) {
                throw new \Exception('Curl Error : '.curl_error($curl));
            }
            curl_close($curl);
            fclose($handle);
        }
    }
}
