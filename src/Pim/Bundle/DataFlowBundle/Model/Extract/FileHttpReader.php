<?php
namespace Pim\Bundle\DataFlowBundle\Model\Extract;

use \Exception as Exception;

/**
 * Reading HTTP file with curl
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FileHttpReader
{
    /**
     * Process reading HTTP file with curl, if login and password are provided use them for basic authentication
     *
     * TODO : replace login / password by option array
     *
     * @param string $url
     * @param string $login
     * @param string $password
     * @return string
     * @throws Exception
     */
    public function process($url, $login = null, $password = null)
    {
        // use curl to get xml product content with basic authentication
        $c = curl_init();
        if (!$c) {
                throw new Exception('Curl not initialized');
        }

        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HEADER, false);
        if ($login and $password) {
            curl_setopt($c, CURLOPT_USERPWD, $login.':'.$password);
        }
        $output = curl_exec($c);

        // deal with curl exception
        if ($output === false) {
                throw new Exception('Curl Error : '.curl_error($c));
        }
        curl_close($c);
        return $output;
    }
}