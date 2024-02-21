<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\EndToEnd;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class LoginRateLimitIntegration extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

    }

    public function test_it_lock_the_user_when_too_many_attempt_occured()
    {
        $client = static::createClient([]);
        $catalogs = static::getContainer()->get('akeneo_integration_tests.catalogs');
        $fixturesLoader = static::getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($catalogs->useTechnicalCatalog());

        $form = $client->request('GET', '/user/login')->selectButton('Login')->form();
        $form['_username'] = 'julia';
        $form['_password'] = 'wrong password';
        $client->submit($form);
        $client->followRedirect()->selectButton('Login')->form();

        $form = $client->request('GET', '/user/login')->selectButton('Login')->form();
        $form['_username'] = 'julia';
        $form['_password'] = 'another wrong password';
        $client->submit($form);

        $text = $client->followRedirect()->text(null, true);
        $this->assertStringContainsString('Your account is locked for 5 minutes after too many authentication attempts.', $text);
    }
}
