<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserConsentedAuthenticationScopesQueryIntegration extends WebTestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_gets_user_consented_scopes_from_the_database(): void
    {

    }

    public function test_it_gets_nothing_if_there_is_no_scopes_in_the_database(): void
    {
    }
}
