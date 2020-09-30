<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Controller\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetRuleDefinitionControllerIntegration extends ControllerIntegrationTestCase
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

    public function test_it_returns_a_rule_definition()
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_get',
            ['ruleCode' => '123'],
            'GET'
        );

        $decodedContent = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $decodedContent);

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_OK,
            '{"id":' . $decodedContent['id'] . ',"code":"123","type":"add","priority":0,"enabled":true,"content":{"conditions":[],"actions":[]},"labels":{"en_US":"123 english","fr_FR":"123 french"}}'
        );
    }

    public function test_it_returns_a_rule_definition_with_conditions_and_actions()
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_get',
            ['ruleCode' => '234'],
            'GET'
        );

        $decodedContent = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $decodedContent);

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_OK,
            '{"id":' . $decodedContent['id'] . ',"code":"234","type":"add","priority":0,"enabled":false,"content":{"conditions":[{"field":"family","operator":"IN","values":["shoes"]}],"actions":[{"type":"clear","field":"category"}]},"labels":[]}'
        );
    }

    public function test_it_returns_an_error_if_the_rule_does_not_exist()
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_get',
            ['ruleCode' => '1'],
            'GET'
        );

        $this->webClientHelper->assertStatusCode($this->client->getResponse(), Response::HTTP_NOT_FOUND);
    }

    private function loadFixtures()
    {
        $ruleDefinitions = [];

        $ruleDefinitions[] = (new RuleDefinition())
            ->setCode('123')
            ->setEnabled(true)
            ->setContent([
                'conditions' => [],
                'actions' => [],
            ])
            ->setType('add')
            ->setId(123456789)
            ->setLabel('en_US', '123 english')
            ->setLabel('fr_FR', '123 french')
        ;

        $ruleDefinitions[] = (new RuleDefinition())
            ->setCode('234')
            ->setEnabled(false)
            ->setContent([
                'conditions' => [
                    ['field' => 'family', 'operator' => 'IN', 'values' => ['shoes']],
                ],
                'actions' => [
                    ['type' => 'clear', 'field' => 'category'],
                ],
            ])
            ->setType('add')
            ->setId(987654321)
        ;

        foreach ($ruleDefinitions as $ruleDefinition) {
            $this->ruleDefinitionSaver->save($ruleDefinition);
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
