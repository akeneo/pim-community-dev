<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Controller\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class UpdateRuleDefinitionControllerIntegration extends ControllerIntegrationTestCase
{
    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var RuleDefinitionSaver */
    private $ruleDefinitionSaver;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->ruleDefinitionSaver = $this->get('akeneo_rule_engine.saver.rule_definition');

        $this->loadFixtures();
    }

    public function test_it_is_unauthorized()
    {
        $this->updateRuleDefinition('123', []);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_FORBIDDEN);
    }

    public function test_it_updates_a_rule_definition()
    {
        $this->enableAcl();
        $normalizedRuleDefinition = [
            'code' => '123',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'a_text', 'value' => 'awesome-jacket'],
                ]
            ],
            'type' => 'product',
            'priority' => 0,
            'labels' => [
                'en_US' => '123 english',
                'fr_FR' => '123 french',
            ]
        ];

        $this->updateRuleDefinition('123', $normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        Assert::arrayHasKey($content, 'id');
        unset($content['id']);

        Assert::assertEqualsCanonicalizing($normalizedRuleDefinition, $content);
    }

    public function test_it_fails_on_non_existing_code()
    {
        $this->enableAcl();
        $normalizedRuleDefinition = [
            'code' => 'abc',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'a_text', 'value' => 'awesome-jacket'],
                ]
            ],
            'type' => 'product',
            'priority' => 0,
        ];

        $this->updateRuleDefinition('abc', $normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_NOT_FOUND);
    }

    private function loadFixtures()
    {
        $ruleDefinition = new RuleDefinition();
        $ruleDefinition
            ->setCode('123')
            ->setContent([
                'conditions' => [],
                'actions' => [
                    ['type' => 'clear', 'field' => 'a_text'],
                ],
            ])
            ->setType('product')
        ;

        $this->ruleDefinitionSaver->save($ruleDefinition);
    }

    private function updateRuleDefinition(string $ruleDefinitionCode, array $normalizedRuleDefinition)
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_update',
            ['ruleDefinitionCode' => $ruleDefinitionCode],
            'PUT',
            [],
            \json_encode($normalizedRuleDefinition)
        );
    }

    private function enableAcl() : void
    {
        $aclManager = $this->get('oro_security.acl.manager');
        $roles = $this->get('pim_user.repository.role')->findAll();

        foreach ($roles as $role) {
            $privilege = new AclPrivilege();
            $identity = new AclPrivilegeIdentity('action:pimee_catalog_rule_rule_edit_permissions');
            $privilege
                ->setIdentity($identity)
                ->addPermission(new AclPermission('EXECUTE', 1));

            $aclManager->getPrivilegeRepository()
                ->savePrivileges(new RoleSecurityIdentity($role), new ArrayCollection([$privilege]));
        }

        $aclManager->flush();
        $aclManager->clearCache();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
