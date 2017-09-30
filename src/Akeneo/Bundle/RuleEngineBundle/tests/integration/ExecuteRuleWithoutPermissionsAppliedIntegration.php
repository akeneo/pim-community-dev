<?php

declare(strict_types=1);

namespace Akeneo\Bundle\RuleEngineBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ExecuteRuleWithoutPermissionsAppliedIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct(
            'product_not_editable_by_redactor_1',
            [
                'categories' => ['categoryA2'],
                'family'     => 'familyA',
                'values'     => [
                    'a_text' => [
                        ['data' => 'a text for category A2', 'locale' => null, 'scope' => null],
                    ]
                ],
            ]
        );

        $this->createProduct(
            'product_not_editable_by_redactor_2',
            [
                'categories' => ['categoryB'],
                'family'     => 'familyA',
                'values'     => [
                    'a_text' => [
                        ['data' => 'a text for category B', 'locale' => null, 'scope' => null],
                    ]
                ],
            ]
        );

        $this->createProduct(
            'product_editable_by_redactor',
            [
                'categories' => ['master'],
                'family'     => 'familyA',
                'values'     => [
                    'a_text' => [
                        ['data' => 'a text for category Master', 'locale' => null, 'scope' => null],
                    ]
                ],
            ]
        );

        $this->get('doctrine')->getManager()->clear();
    }

    public function testRuleExecutionOnAllProducts(): void
    {
        $this->launchRulesExecution('akeneo:rule:run', 'mary', 'clear_a_text');

        $product1 = $this->getProduct('product_not_editable_by_redactor_1');
        $this->assertSame('Nice description for all', $product1->getValue('a_text')->getData());

        $product2 = $this->getProduct('product_not_editable_by_redactor_2');
        $this->assertSame('Nice description for all', $product2->getValue('a_text')->getData());

        $product3 = $this->getProduct('product_editable_by_redactor');
        $this->assertSame('Nice description for all', $product3->getValue('a_text')->getData());
    }

    /**
     * @param string      $command
     * @param string|null $username
     * @param string|null $ruleCode
     *
     * @return BufferedOutput
     */
    protected function launchRulesExecution(string $command, string $username = null, string $ruleCode = null): BufferedOutput
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $arrayInput = [
            'command'  => $command,
            '-v' => true,
        ];

        if (null !== $username) {
            $arrayInput['--username'] = $username;
        }

        if (null !== $ruleCode) {
            $arrayInput['code'] = $ruleCode;
        }

        $input = new ArrayInput($arrayInput);

        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output;
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    protected function getProduct(string $identifier): ProductInterface
    {
        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return new Configuration(
            [
                Configuration::getTechnicalCatalogPath(),
                $rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'technical'
            ]
        );
    }
}
