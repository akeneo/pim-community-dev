<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Controller\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateRuleDefinitionControllerIntegration extends ControllerIntegrationTestCase
{
    /** @var WebClientHelper  */
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
        $this->createRuleDefinition([]);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_FORBIDDEN);
    }

    public function test_it_creates_a_rule_definition()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_create_permissions');
        $normalizedRuleDefinition = [
            'code' => '345',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'a_text', 'value' => 'awesome-jacket'],
                ]
            ],
            'priority' => 0,
            'labels' => [
                'en_US' => '345 english',
                'fr_FR' => '345 french',
            ]
        ];

        $this->createRuleDefinition($normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        Assert::assertArrayHasKey('id', $content);

        $expectedContent = $normalizedRuleDefinition;
        $expectedContent['id'] = $content['id'];
        $expectedContent['type'] = 'product';
        $expectedContent['enabled'] = true;

        ksort($expectedContent);
        ksort($content);
        Assert::assertEquals($content, $expectedContent);
    }

    public function test_it_creates_a_disabled_rule_definition()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_create_permissions');
        $normalizedRuleDefinition = [
            'code' => '345',
            'enabled' => false,
            'content' => ['conditions' => [], 'actions' => []],
            'priority' => 10,
            'labels' => []
        ];

        $this->createRuleDefinition($normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        Assert::assertFalse($content['enabled']);
    }

    public function test_it_creates_a_rule_definition_with_only_code()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_create_permissions');
        $normalizedRuleDefinition = [
            'code' => 'my_new_code',
            'enabled' => true,
            'content' => ['conditions' => [], 'actions' => []],
        ];

        $this->createRuleDefinition($normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        Assert::arrayHasKey($content, 'id');

        $expectedContent = $normalizedRuleDefinition;
        $expectedContent['id'] = $content['id'];
        $expectedContent['type'] = 'product';
        $expectedContent['labels'] = [];
        $expectedContent['priority'] = 0;

        ksort($expectedContent);
        ksort($content);
        Assert::assertEquals($expectedContent, $content);
    }

    public function test_it_fails_on_existing_code()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_create_permissions');
        $normalizedRuleDefinition = [
            'code' => '123',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'a_text', 'value' => 'awesome-jacket'],
                ]
            ],
            'priority' => 0,
        ];

        $this->createRuleDefinition($normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    public function test_if_fails_with_a_wrong_format()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_create_permissions');
        $normalizedRuleDefinition = [
            'code' => 'abc',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'a_text', 'value' => 'awesome-jacket'],
                ]
            ],
            'priority' => 'toto',
        ];

        $this->createRuleDefinition($normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    private function loadFixtures()
    {
        $ruleDefinition = new RuleDefinition();
        $ruleDefinition
            ->setCode('123')
            ->setContent([
                'conditions' => [],
                'actions' => [],
            ])
            ->setType('add')
        ;

        $this->ruleDefinitionSaver->save($ruleDefinition);
    }

    private function createRuleDefinition(array $normalizedRuleDefinition)
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_create',
            [],
            'POST',
            [],
            \json_encode($normalizedRuleDefinition)
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
