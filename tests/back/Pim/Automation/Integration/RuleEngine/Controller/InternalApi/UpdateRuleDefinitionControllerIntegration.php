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

    public function test_it_updates_a_rule_defintion()
    {
        $normalizedRuleDefinition = [
            'code' => '123',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ]
            ],
            'type' => 'add',
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

        Assert::assertEqualsCanonicalizing($content, $normalizedRuleDefinition);
    }

    public function test_it_fails_on_non_existing_code()
    {
        $normalizedRuleDefinition = [
            'code' => 'abc',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ]
            ],
            'type' => 'add',
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
                    ['type' => 'clear', 'field' => 'name'],
                ],
            ])
            ->setType('add')
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
