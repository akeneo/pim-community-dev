<?php
namespace Oro\Bundle\TestFrameworkBundle\Test;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Response;

class ToolsAPI
{
    /** Default WSSE credentials */
    const USER_NAME = 'admin';
    const USER_PASSWORD = 'admin_api_key';
    const NONCE = 'd36e316282959a9ed4c89851497a717f';

    /**  Default user name and password */
    const AUTH_USER = 'admin@example.com';
    const AUTH_PW = 'admin';


    protected static $random = null;

    /**
     * Generate WSSE authorization header
     */
    public static function generateWsseHeader($userName = self::USER_NAME, $userPassword = self::USER_PASSWORD, $nonce = self::NONCE)
    {
        $created  = date('c');
        $digest   = base64_encode(sha1(base64_decode($nonce) . $created . $userPassword, true));
        $wsseHeader = array(
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Authorization' => 'WSSE profile="UsernameToken"',
            'HTTP_X-WSSE' => sprintf(
                'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                $userName,
                $digest,
                $nonce,
                $created
            )
        );
        return $wsseHeader;
    }

    /**
     * Generate Basic  authorization header
     */
    public static function generateBasicHeader($userName = self::AUTH_USER, $userPassword = self::AUTH_PW)
    {
        $basicHeader = array('PHP_AUTH_USER' =>  $userName, 'PHP_AUTH_PW' => $userPassword);
        return $basicHeader;
    }

    /**
     * Data provider for REST/SOAP API tests
     *
     * @param $folder
     *
     * @return array
     */
    public static function requestsApi($folder)
    {
        $parameters = array();
        $testFiles = new \RecursiveDirectoryIterator(
            $folder,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        foreach ($testFiles as $fileName => $object) {
            $parameters[$fileName] = Yaml::parse($fileName);
            if (is_null($parameters[$fileName]['response'])) {
                unset($parameters[$fileName]['response']);
            }
        }
        //generate unique value
        if (is_null(self::$random)) {
            self::$random = self::randomGen(5);
        }

        foreach ($parameters as $key => $value) {
            array_walk(
                $parameters[$key]['request'],
                array(get_called_class(), 'replace'),
                self::$random
            );
            array_walk(
                $parameters[$key]['response'],
                array(get_called_class(), 'replace'),
                self::$random
            );
        }

        return
            $parameters;
    }

    /**
     * Test API response
     *
     * @param array $response
     * @param $result
     * @param $debugInfo
     */
    public static function assertEqualsResponse($response, $result, $debugInfo = '')
    {
        \PHPUnit_Framework_TestCase::assertEquals($response['return'], $result, $debugInfo);
    }

    /**
     * Test API response status
     *
     * @param Response $response
     * @param int    $statusCode
     * @param string $contentType
     */
    public static function assertJsonResponse($response, $statusCode = 201, $contentType = 'application/json')
    {
        \PHPUnit_Framework_TestCase::assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $response->getContent()
        );

        if ($contentType !== '') {
            \PHPUnit_Framework_TestCase::assertTrue(
                $response->headers->contains('Content-Type', $contentType),
                $response->headers
            );
        }
    }

    /**
     * Convert stdClass to array
     *
     * @param $class
     * @return array
     */
    public static function classToArray($class)
    {
        return json_decode(json_encode($class), true);
    }

    /**
     * Convert json to array
     *
     * @param $json
     * @return array
     */
    public static function jsonToArray($json)
    {
        return json_decode($json, true);
    }

    /**
     * @param $length
     * @return string
     */
    public static function randomGen($length)
    {
        $random= "";
        srand((double) microtime()*1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890_";
        // Add the special characters to $char_list if needed

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($char_list, (rand()%(strlen($char_list))), 1);
        }
        self::$random = $random;

        return $random;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    /**
     * @param $value
     * @param $key
     * @param $random
     */
    public static function replace(&$value, $key, $random)
    {
        if (!is_null($value)) {
            $value = str_replace('%str%', $random, $value);
        }
    }

    /**
     * @param Client $test
     * @param $gridName
     * @param array $filter
     * @return null|\Symfony\Component\HttpFoundation\Response
    */
    public static function getEntityGrid($test, $gridName, $filter = array())
    {
        $test->request(
            'GET',
            $test->generate('oro_datagrid_index', array('gridName' => $gridName)),
            $filter
        );

        return $test->getResponse();
    }
}
