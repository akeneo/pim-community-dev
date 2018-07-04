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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Doctrine\Repository;

use Akeneo\Test\Integration\Configuration as TestConfiguration;
use Akeneo\Test\Integration\TestCase;
use PimEnterprise\Component\SuggestData\Model\Configuration;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationRepositoryIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_saves_a_suggest_data_configuration()
    {
        $configuration = new Configuration('pim-ai', ['token' => 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj']);

        $this->get('pimee_suggest_data.repository.configuration')->save($configuration);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT entity, name, value from oro_config INNER JOIN oro_config_value o on oro_config.id = o.config_id;'
        );
        $retrievedConfiguration = $statement->fetchAll();

        $this->assertSame([[
            'entity' => 'pim-ai',
            'name' => 'token',
            'value' => 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj',
        ]], $retrievedConfiguration);
    }

    /**
     * @test
     */
    public function it_updates_a_suggest_data_configuration()
    {
        $configuration = new Configuration('pim-dot-ai', ['token' => 'a_first_token']);
        $this->get('pimee_suggest_data.repository.configuration')->save($configuration);

        $configuration->setValues(['token' => 'a_new_token']);
        $this->get('pimee_suggest_data.repository.configuration')->save($configuration);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT entity, name, value from oro_config INNER JOIN oro_config_value o on oro_config.id = o.config_id;'
        );
        $retrievedConfiguration = $statement->fetchAll();

        $this->assertSame([[
            'entity' => 'pim-dot-ai',
            'name' => 'token',
            'value' => 'a_new_token',
        ]], $retrievedConfiguration);
    }

    /**
     * @test
     */
    public function it_finds_a_suggest_data_configuration()
    {
        $configuration = new Configuration('pim-ai', ['token' => 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj']);

        $repository = $this->get('pimee_suggest_data.repository.configuration');
        $repository->save($configuration);

        $retrievedConfiguration = $repository->findOneByCode('pim-ai');
        $this->assertInstanceOf(Configuration::class, $retrievedConfiguration);
        $this->assertSame([
            'code' => 'pim-ai',
            'values' => ['token' => 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj'],
        ], $retrievedConfiguration->normalize());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): TestConfiguration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
