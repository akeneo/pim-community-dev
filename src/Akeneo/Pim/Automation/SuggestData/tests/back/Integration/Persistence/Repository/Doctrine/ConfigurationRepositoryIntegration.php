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

namespace Akeneo\Test\Pim\Automation\SuggestData\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Test\Integration\Configuration as TestConfiguration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationRepositoryIntegration extends TestCase
{
    public function test_it_saves_a_suggest_data_configuration(): void
    {
        $tokenString = 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj';
        $configuration = new Configuration();
        $configuration->setToken(new Token($tokenString));

        $this->getRepository()->save($configuration);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT entity, name, value from oro_config INNER JOIN oro_config_value o on oro_config.id = o.config_id;'
        );
        $retrievedConfiguration = $statement->fetchAll();

        $this->assertSame([[
            'entity' => 'franklin',
            'name' => 'token',
            'value' => $tokenString,
        ]], $retrievedConfiguration);
    }

    public function test_it_updates_a_suggest_data_configuration(): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token('a_first_token'));
        $this->getRepository()->save($configuration);

        $this->get('doctrine.orm.entity_manager')->clear();

        $configuration->setToken(new Token('a_new_token'));
        $this->getRepository()->save($configuration);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query(
            'SELECT entity, name, value from oro_config INNER JOIN oro_config_value o on oro_config.id = o.config_id;'
        );
        $retrievedConfiguration = $statement->fetchAll();

        $this->assertSame([[
            'entity' => 'franklin',
            'name' => 'token',
            'value' => 'a_new_token',
        ]], $retrievedConfiguration);
    }

    public function test_it_finds_a_suggest_data_configuration(): void
    {
        $tokenString = 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj';
        $configuration = new Configuration();
        $configuration->setToken(new Token($tokenString));

        $this->getRepository()->save($configuration);

        $this->get('doctrine.orm.entity_manager')->clear();

        $retrievedConfiguration = $this->getRepository()->find();
        $this->assertInstanceOf(Configuration::class, $retrievedConfiguration);
        $this->assertInstanceOf(Token::class, $retrievedConfiguration->getToken());
        $this->assertSame($tokenString, (string) $retrievedConfiguration->getToken());
    }

    public function test_it_removes_a_suggest_data_configuration(): void
    {
        $tokenString = 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj';
        $configuration = new Configuration();
        $configuration->setToken(new Token($tokenString));

        $this->getRepository()->save($configuration);
        $this->get('doctrine.orm.entity_manager')->clear();

        $this->getRepository()->clear();
        $this->get('doctrine.orm.entity_manager')->clear();

        $retrievedConfiguration = $this->getRepository()->find();
        $this->assertInstanceOf(Configuration::class, $retrievedConfiguration);
        $this->assertNull($retrievedConfiguration->getToken());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): TestConfiguration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return ConfigurationRepositoryInterface
     */
    private function getRepository(): ConfigurationRepositoryInterface
    {
        return $this->get('akeneo.pim.automation.suggest_data.repository.configuration');
    }
}
