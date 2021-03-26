<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Controller\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver\RuleDefinitionSaver;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use AkeneoEnterprise\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use AkeneoTestEnterprise\Pim\Automation\Integration\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChangeRuleDefinitionStatusControllerIntegration extends ControllerIntegrationTestCase
{
    /** @var WebClientHelper */
    private $webClientHelper;

    /** @var RuleDefinitionSaver */
    private $ruleDefinitionSaver;

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($this->client, 'julia');
        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->ruleDefinitionSaver = $this->get('akeneo_rule_engine.saver.rule_definition');
        $this->ruleDefinitionRepository = $this->get('akeneo_rule_engine.repository.rule_definition');

        $this->loadFixtures();
    }

    public function test_it_is_unauthorized()
    {
        $this->doRequest('foo', true);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_FORBIDDEN);
    }

    public function test_it_enables_a_rule_definition()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_edit_permissions');
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier('foo');
        $this->assertNotNull($ruleDefinition);
        $this->assertFalse($ruleDefinition->isEnabled());

        $this->doRequest('foo', true);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_NO_CONTENT);

        $this->clearCache();
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier('foo');
        $this->assertNotNull($ruleDefinition);
        $this->assertTrue($ruleDefinition->isEnabled());
    }

    public function test_it_disables_a_rule_definition()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_edit_permissions');
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier('bar');
        $this->assertNotNull($ruleDefinition);
        $this->assertTrue($ruleDefinition->isEnabled());

        $this->doRequest('bar', false);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_NO_CONTENT);

        $this->clearCache();
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier('bar');
        $this->assertNotNull($ruleDefinition);
        $this->assertFalse($ruleDefinition->isEnabled());
    }

    public function test_it_cannot_enable_an_unknown_rule_definition()
    {
        $this->enableAcl('action:pimee_catalog_rule_rule_edit_permissions');

        $this->doRequest('unknown', true);

        $response = $this->client->getResponse();
        Assert::assertSame($response->getStatusCode(), Response::HTTP_NOT_FOUND);
    }

    private function doRequest(string $ruleDefinitionCode, bool $enabled): void
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pimee_enrich_rule_definition_status_change',
            ['code' => $ruleDefinitionCode],
            Request::METHOD_PUT,
            [],
            \json_encode(['enabled' => $enabled])
        );
    }

    private function loadFixtures()
    {
        $ruleDefinition = new RuleDefinition();
        $ruleDefinition
            ->setCode('foo')
            ->setContent([
                'conditions' => [],
                'actions' => [['type' => 'clear', 'field' => 'a_text']],
            ])
            ->setType('product')
            ->setEnabled(false)
        ;

        $this->ruleDefinitionSaver->save($ruleDefinition);
        $ruleDefinition = new RuleDefinition();
        $ruleDefinition
            ->setCode('bar')
            ->setContent([
                'conditions' => [],
                'actions' => [['type' => 'clear', 'field' => 'a_text']],
            ])
            ->setType('product')
            ->setEnabled(true)
        ;
        $this->ruleDefinitionSaver->save($ruleDefinition);
    }

    private function clearCache(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
