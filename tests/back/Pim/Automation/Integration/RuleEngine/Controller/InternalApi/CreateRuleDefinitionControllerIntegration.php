<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Controller\InternalApi;

use Akeneo\ReferenceEntity\Common\Helper\WebClientHelper;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateRuleDefinitionControllerIntegration extends ControllerIntegrationTestCase
{
    // TODO This is the ref entity one. We need ourself.
    /** @var WebClientHelper  */
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

    public function test_it_creates_a_rule_defintion()
    {
        $normalizedRuleDefinition = [
            'code' => '345',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ]
            ],
            'priority' => 0,
        ];

        $this->createRuleDefinition($normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        Assert::arrayHasKey($content, 'id');

        $expectedContent = $normalizedRuleDefinition;
        $expectedContent['id'] = $content['id'];
        $expectedContent['type'] = 'product';

        ksort($expectedContent);
        ksort($content);
        Assert::assertEquals($content, $expectedContent);
    }

    public function test_it_fails_on_existing_code()
    {
        $normalizedRuleDefinition = [
            'code' => '123',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ]
            ],
            'priority' => 0,
        ];

        $this->createRuleDefinition($normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }

    /**
     * This test is deactivated because errors are not managed yet.
     * TODO RUL-84
     */
    /*
    public function test_if_fails_with_a_wrong_format()
    {
        $normalizedRuleDefinition = [
            'code' => 'abc',
            'content' => [
                'conditions' => [],
                'actions' => [
                    ['type' => 'set', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ]
            ],
            'priority' => 'toto',
        ];

        $this->createRuleDefinition($normalizedRuleDefinition);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_BAD_REQUEST);
    }*/

    private function loadFixtures()
    {
        $ruleDefinition = new RuleDefinition();
        $ruleDefinition
            ->setCode('123')
            ->setContent([
                'conditions' => [],
                'actions' => ['action1', 'action2'],
            ])
            ->setType('add')
        ;

        $this->ruleDefinitionRepository->save($ruleDefinition);
    }

    private function createRuleDefinition(array $normalizedRuleDefinition)
    {
        $this->webClientHelper->callRoute(
            $this->client,
            'pimee_enrich_rule_definition_create',
            [],
            'POST',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
            $normalizedRuleDefinition
        );
    }
}
