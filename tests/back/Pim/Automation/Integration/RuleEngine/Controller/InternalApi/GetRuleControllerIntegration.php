<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Controller\InternalApi;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class GetRuleControllerIntegration extends ControllerIntegrationTestCase
{
    private $webClientHelper;
    private $ruleDefinitionRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneoreference_entity.tests.helper.authenticated_client')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneoreference_entity.tests.helper.web_client_helper');
        $this->ruleDefinitionRepository = $this->get('akeneo_rule_engine.repository.rule_definition');

        $this->loadFixtures();
    }

    public function test_it_returns_a_rule_definition()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            'pimee_enrich_rule_definition_get',
            ['ruleCode' => '123'],
            'GET',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_OK,
            '{"id":null,"code":"123","type":"add","priority":0,"content":{"conditions":[],"actions":["action1","action2"]}}'
        );
    }

    public function test_it_returns_a_rule_definition_with_conditions()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            'pimee_enrich_rule_definition_get',
            ['ruleCode' => '234'],
            'GET',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_OK,
            '{"id":null,"code":"234","type":"add","priority":0,"content":{"conditions":["condition1"],"actions":["action3","action4"]}}'
        );
    }

    public function test_it_returns_an_error_if_the_rule_does_not_exist()
    {
        $this->webClientHelper->callRoute(
            $this->client,
            'pimee_enrich_rule_definition_get',
            ['ruleCode' => '1'],
            'GET',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE'          => 'application/json',
            ]
        );

        $this->webClientHelper->assertResponse(
            $this->client->getResponse(),
            Response::HTTP_NOT_FOUND
        );
    }


    private function loadFixtures()
    {
        $ruleDefinitions = [];

        $ruleDefinitions[] = (new RuleDefinition())
            ->setCode('123')
            ->setContent([
                'conditions' => [],
                'actions' => ['action1', 'action2'],
            ])
            ->setType('add')
        ;

        $ruleDefinitions[] = (new RuleDefinition())
            ->setCode('234')
            ->setContent([
                'conditions' => ['condition1'],
                'actions' => ['action3', 'action4'],
            ])
            ->setType('add')
        ;

        foreach($ruleDefinitions as $ruleDefinition) {
            $this->ruleDefinitionRepository->save($ruleDefinition);
        }
    }
}
