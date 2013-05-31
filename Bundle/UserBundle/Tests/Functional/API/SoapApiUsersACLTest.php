<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapApiUsersACLTest extends WebTestCase
{

    const USER_NAME = 'user_wo_permissions';
    const USER_PASSWORD = 'user_api_key';

    const DEFAULT_USER_ID = '1';

    protected $clientSoap = null;
    protected static $hasLoaded = false;

    public function setUp()
    {
        $this->clientSoap = static::createClient(array(), ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD));
        if (!self::$hasLoaded) {
            $this->clientSoap->appendFixtures(__DIR__ . DIRECTORY_SEPARATOR . 'DataFixtures');
        }
        self::$hasLoaded = true;
    }

    public function testWsseAccess()
    {
        try {
            $this->clientSoap->soap(
                "http://localhost/api/soap",
                array(
                    'location' => 'http://localhost/api/soap',
                    'soap_version' => SOAP_1_2
                )
            );
        } catch (\Exception $e) {
            $this->assertEquals('Forbidden', $e->getMessage());
        }
    }
}
