<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\Element;
use Context\Spin\SpinCapableTrait;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppActivateContext extends PimContext
{
    use SpinCapableTrait;

    private ?array $connectedApp = null;
    private bool $aNewTabHasBeenOpened = false;

    /**
     * @Given I should see :appName app
     */
    public function iShouldSeeApp(string $appName)
    {
        /** @var Element $page */
        $page = $this->getCurrentPage();

        $appTitle = $this->spin(function () use ($appName, $page) {
            return $page->find('named', ['content', $appName]);
        }, sprintf('Cannot find the %s app', $appName));

        Assert::assertNotNull($appTitle);
    }

    /**
     * @When I click on :appName activate button
     */
    public function iClickOnActivateButton(string $appName)
    {
        $session = $this->getSession();
        /** @var Element $page */
        $page = $this->getCurrentPage();

        $titleNode = $page->find('named', ['content', $appName]);

        $cardContainer = $titleNode->getParent()->getParent();

        $link = $cardContainer->find('named', ['content', 'Connect']);
        Assert::assertNotNull($link);

        $link->click();

        Assert::assertCount(2, $this->getSession()->getWindowNames());

        $this->aNewTabHasBeenOpened();

        $windows = $session->getWindowNames();
        $session->switchToWindow($windows[1]);
    }

    /**
     * @When the url matches :url
     */
    public function theUrlMatches(string $url)
    {
        $session = $this->getSession();

        $this->spin(function () use ($session, $url) {
            return $url === $session->getCurrentUrl()
                || preg_match(sprintf('|^%s$|', $url), $session->getCurrentUrl());
        }, sprintf('Current url is not %s, got %s', $url, $session->getCurrentUrl()));
    }

    /**
     * @When I click on the button :label
     */
    public function iClickOnTheButton($label)
    {
        $button = $this->spin(function () use ($label) {
            /** @var Element $page */
            $page = $this->getCurrentPage();

            return $page->find('named', ['link_or_button', $label]);
        }, sprintf('Button with label "%s" not found', $label));

        $button->click();
    }

    /**
     * @When I click on the consent checkbox
     */
    public function iClickOnTheConsentCheckbox(): void
    {
        $checkbox = $this->spin(function () {
            /** @var Element $page */
            $page = $this->getCurrentPage();

            return $page->find('css', '[role=checkbox]');
        }, 'Consent checkbox not found');

        $checkbox->click();
    }

    /**
     * @When I see :text
     */
    public function iSee($text)
    {
        $this->spin(function () use ($text) {
            /** @var Element $page */
            $page = $this->getCurrentPage();

            return $page->find('named', ['content', $text]);
        }, sprintf('Element with text "%s" not found', $text));
    }

    /**
     * @Then I have the connected app :name
     */
    public function iHaveTheConnectedApp($name)
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->getMainContext()->getContainer()->get('doctrine.dbal.default_connection');

        $connectedApp = $this->spin(function () use ($name, $connection): ?array {
            $query = <<<SQL
SELECT *
FROM akeneo_connectivity_connected_app
WHERE name = :name
SQL;

            return $connection->fetchAssociative($query, [
                'name' => $name,
            ]) ?: null;
        }, sprintf('Connected app "%s" not found', $name));

        $this->connectedApp = $connectedApp;
    }

    /**
     * @Then my connected app has the following ACLs:
     */
    public function myConnectedAppHasTheFollowingACLs(TableNode $table)
    {
        $roles = $this->getConnectedAppRoles();

        if (empty($roles)) {
            throw new \LogicException('There is no roles in the connected app');
        }

        $acls = [];
        $hash = $table->getHash();
        foreach ($hash as $row) {
            $acls[$row['name']] = $row['enabled'] === 'true';
        }

        $this->assertRoleAclsAreGranted($roles, $acls);
    }

    /**
     * @Then it can exchange the authorization code for a token
     */
    public function itCanExchangeTheAuthorizationCodeForAToken()
    {
        if ($this->connectedApp === null) {
            throw new \LogicException('There is no connected app in the Context');
        }

        $code = $this->getCreatedAuthorizationCode();

        $client = new KernelBrowser($this->getKernel());
        $client->request(
            'POST',
            '/connect/apps/v1/oauth2/token',
            [
                'client_id' => $this->connectedApp['id'],
                'code' => $code,
                'code_identifier' => 'foo',
                'code_challenge' => 'bar',
                'grant_type' => 'authorization_code',
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            ],
        );

        $response = $client->getResponse();
        $payload = json_decode($response->getContent(), true);

        Assert::assertEquals([
            'access_token',
            'token_type',
            'scope',
        ], array_keys($payload));

        Assert::assertEquals('bearer', $payload['token_type']);

        $scopes = \explode(' ', $payload['scope']);
        Assert::assertContains('read_products', $scopes);
        Assert::assertContains('write_products', $scopes);
        Assert::assertContains('delete_products', $scopes);
    }

    /**
     * @Then it can exchange the authorization code for an id token
     */
    public function itCanExchangeTheAuthorizationCodeForAnIdToken()
    {
        if ($this->connectedApp === null) {
            throw new \LogicException('There is no connected app in the Context');
        }

        $code = $this->getCreatedAuthorizationCode();

        $client = new KernelBrowser($this->getKernel());
        $client->request(
            'POST',
            '/connect/apps/v1/oauth2/token',
            [
                'client_id' => $this->connectedApp['id'],
                'code' => $code,
                'code_identifier' => 'foo',
                'code_challenge' => 'bar',
                'grant_type' => 'authorization_code',
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            ],
        );

        $response = $client->getResponse();
        $payload = json_decode($response->getContent(), true);

        Assert::assertEquals([
            'access_token',
            'token_type',
            'scope',
            'id_token',
        ], array_keys($payload));

        Assert::assertEquals('bearer', $payload['token_type']);

        $scopes = \explode(' ', $payload['scope']);
        Assert::assertContains('read_products', $scopes);
        Assert::assertContains('write_products', $scopes);
        Assert::assertContains('delete_products', $scopes);
        Assert::assertContains('openid', $scopes);
        Assert::assertContains('profile', $scopes);
        Assert::assertContains('email', $scopes);
    }

    /** @AfterScenario */
    public function closeOpenedTabs(): void
    {
        if ($this->aNewTabHasBeenOpened) {
            $this->getSession()->restart();
        }
    }

    private function getCreatedAuthorizationCode(): ?string
    {
        if ($this->connectedApp === null) {
            throw new \LogicException('There is no connected app in the Context');
        }

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->getMainContext()->getContainer()->get('doctrine.dbal.default_connection');

        $query = <<<SQL
SELECT pim_api_auth_code.token
FROM pim_api_auth_code
JOIN pim_api_client on pim_api_auth_code.client_id = pim_api_client.id
JOIN akeneo_connectivity_connection on pim_api_client.id = akeneo_connectivity_connection.client_id
JOIN akeneo_connectivity_connected_app ON akeneo_connectivity_connected_app.connection_code = akeneo_connectivity_connection.code
WHERE akeneo_connectivity_connected_app.id = :id
SQL;

        return $connection->fetchOne($query, [
            'id' => $this->connectedApp['id'],
        ]) ?: null;
    }

    private function assertRoleAclsAreGranted(array $roles, array $acls): void
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->getMainContext()->getContainer()->get('oro_security.acl.manager');

        $aclManager->flush();
        $aclManager->clearCache();

        /** @var AccessDecisionManagerInterface $decisionManager */
        $decisionManager = $this->getMainContext()->getContainer()->get('security.access.decision_manager');
        $token = new UsernamePasswordToken('username', 'main', $roles);

        foreach ($acls as $acl => $expectedValue) {
            assert(is_bool($expectedValue));

            $isAllowed = $decisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));

            if ($expectedValue !== $isAllowed) {
                throw new \LogicException(sprintf('ACL is invalid: %s %s', implode(',', $roles), $acl));
            }
        }
    }

    private function getConnectedAppRoles(): array
    {
        if ($this->connectedApp === null) {
            throw new \LogicException('There is no connected app in the Context');
        }

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->getMainContext()->getContainer()->get('doctrine.dbal.default_connection');

        $query = <<<SQL
SELECT oro_access_role.role
FROM oro_access_role
JOIN oro_user_access_role ON oro_user_access_role.role_id = oro_access_role.id
JOIN akeneo_connectivity_connection ON akeneo_connectivity_connection.user_id = oro_user_access_role.user_id
JOIN akeneo_connectivity_connected_app ON akeneo_connectivity_connected_app.connection_code = akeneo_connectivity_connection.code
WHERE akeneo_connectivity_connected_app.id = :id
SQL;

        return $connection->fetchAssociative($query, [
            'id' => $this->connectedApp['id'],
        ]) ?: [];
    }

    private function aNewTabHasBeenOpened(): void
    {
        $this->aNewTabHasBeenOpened = true;
    }
}
