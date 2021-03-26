<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Controller\InternalApi;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\CreateOrUpdateRuleCommand;
use Akeneo\Pim\Automation\RuleEngine\Component\Command\CreateOrUpdateRuleDefinitionHandler;
use Akeneo\Test\Integration\Configuration;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;

final class DuplicateRuleDefinitionControllerIntegration extends ControllerIntegrationTestCase
{
    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var CreateOrUpdateRuleDefinitionHandler */
    private $createRuleHandler;

    /**
     * @test
     */
    function it_returns_an_unauthorized_response_without_the_proper_permissions()
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_duplicate',
            ['originalRuleCode' => 'enable_all_products_in_familyA'],
            'POST',
            [],
            \json_encode(
                [
                    'code' => 'duplicated',
                    'labels' => ['en_US' => 'Duplicated label'],
                ]
            )
        );
        $response = $this->client->getResponse();
        Assert::assertSame(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_duplicates_an_existing_rule_and_disabled_the_new_one()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_edit_permissions');
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_duplicate',
            ['originalRuleCode' => 'enable_all_products_in_familyA'],
            'POST',
            [],
            \json_encode(
                [
                    'code' => 'duplicated',
                    'labels' => ['en_US' => 'Duplicated label'],
                ]
            )
        );
        $response = $this->client->getResponse();
        Assert::assertSame(200, $response->getStatusCode());
        $body = \json_decode($response->getContent(), true);
        Assert::assertArrayHasKey('id', $body);
        unset($body['id']);

        Assert::assertEqualsCanonicalizing(
            [
                'code' => 'duplicated',
                'type' => 'product',
                'priority' => 25,
                'content' => [
                    'actions' => [
                        [
                            'type' => 'set',
                            'field' => 'enabled',
                            'value' => true,
                        ],
                    ],
                    'conditions' => [
                        [
                            'field' => 'family',
                            'operator' => 'IN',
                            'value' => ['familyA'],
                        ],
                        [
                            'field' => 'enabled',
                            'operator' => '=',
                            'value' => false,
                        ],
                    ],
                ],
                'enabled' => false,
                'labels' => [
                    'en_US' => 'Duplicated label',
                ],
            ],
            $body
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_if_the_code_already_exists()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_edit_permissions');
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_duplicate',
            ['originalRuleCode' => 'enable_all_products_in_familyA'],
            'POST',
            [],
            \json_encode(
                [
                    'code' => 'other_rule',
                ]
            )
        );
        $response = $this->client->getResponse();
        Assert::assertSame(400, $response->getStatusCode());
        $body = \json_decode($response->getContent(), true);
        Assert::assertEqualsCanonicalizing(
            [
                [
                    'path' => 'code',
                    'message' => 'pimee_catalog_rule.form.creation.constraint.code.unique',
                    'global' => false,
                ],
            ],
            $body
        );
    }

    /**
     * @test
     */
    public function it_returns_not_found_if_the_original_rule_does_not_exist()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_edit_permissions');
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_duplicate',
            ['originalRuleCode' => 'unknown_rule'],
            'POST',
            [],
            \json_encode(
                [
                    'code' => 'duplicated_rule',
                ]
            )
        );

        $response = $this->client->getResponse();
        Assert::assertSame(404, $response->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->createRuleHandler = $this->get(
            'Akeneo\Pim\Automation\RuleEngine\Component\Command\CreateOrUpdateRuleDefinitionHandler'
        );

        $this->loadFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function loadFixtures(): void
    {
        $command = new CreateOrUpdateRuleCommand(
            [
                'code' => 'enable_all_products_in_familyA',
                'priority' => 25,
                'actions' => [
                    [
                        'type' => 'set',
                        'field' => 'enabled',
                        'value' => true,
                    ],
                ],
                'conditions' => [
                    [
                        'field' => 'family',
                        'operator' => 'IN',
                        'value' => ['familyA'],
                    ],
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => false,
                    ],
                ],
                'enabled' => true,
                'labels' => [
                    'en_US' => 'English original label',
                    'fr_FR' => 'Label français original',
                ],
            ]
        );
        $violations = $this->get('validator')->validate($command, null, ['import']);
        Assert::assertCount(0, $violations);
        ($this->createRuleHandler)($command);

        $otherCommand = new CreateOrUpdateRuleCommand(
            [
                'code' => 'other_rule',
                'actions' => [
                    [
                        'type' => 'set',
                        'field' => 'family',
                        'value' => 'familyA',
                    ],
                ],
                'conditions' => [],
                'enabled' => true,
            ]
        );
        $violations = $this->get('validator')->validate($otherCommand, null, ['import']);
        Assert::assertCount(0, $violations);
        ($this->createRuleHandler)($otherCommand);
    }
}
