<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\SuggestData\Integration\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Doctrine\ConfigurationRepository;
use Akeneo\Test\Integration\Configuration as TestConfiguration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationRepositoryIntegration extends TestCase
{
    public function test_it_saves_a_suggest_data_configuration(): void
    {
        $token = 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj';
        $configuration = new Configuration(['token' => $token]);

        $this->getRepository()->save($configuration);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT entity, name, value from oro_config INNER JOIN oro_config_value o on oro_config.id = o.config_id;'
        );
        $retrievedConfiguration = $statement->fetchAll();

        $this->assertSame([[
            'entity' => 'pim-ai',
            'name' => 'token',
            'value' => $token,
        ]], $retrievedConfiguration);
    }

    public function test_it_updates_a_suggest_data_configuration(): void
    {
        $configuration = new Configuration(['token' => 'a_first_token']);
        $this->getRepository()->save($configuration);

        $this->get('doctrine.orm.entity_manager')->clear();

        $configuration->setValues(['token' => 'a_new_token']);
        $this->get('akeneo.pim.automation.suggest_data.repository.configuration')->save($configuration);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT entity, name, value from oro_config INNER JOIN oro_config_value o on oro_config.id = o.config_id;'
        );
        $retrievedConfiguration = $statement->fetchAll();

        $this->assertSame([[
            'entity' => 'pim-ai',
            'name' => 'token',
            'value' => 'a_new_token',
        ]], $retrievedConfiguration);
    }

    public function test_it_finds_a_suggest_data_configuration(): void
    {
        $token = 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj';
        $configuration = new Configuration(['token' => $token]);

        $repository = $this->get('akeneo.pim.automation.suggest_data.repository.configuration');
        $repository->save($configuration);

        $this->get('doctrine.orm.entity_manager')->clear();
        $retrievedConfiguration = $repository->find();
        $this->assertInstanceOf(Configuration::class, $retrievedConfiguration);
        $this->assertSame([
            'code' => Configuration::PIM_AI_CODE,
            'values' => ['token' => $token],
        ], $retrievedConfiguration->normalize());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): TestConfiguration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return ConfigurationRepository
     */
    private function getRepository()
    {
        return $this->get('akeneo.pim.automation.suggest_data.repository.configuration');
    }
}
