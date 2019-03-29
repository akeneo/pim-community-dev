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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Test\Integration\Configuration as TestConfiguration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConfigurationRepositoryIntegration extends TestCase
{
    public function test_it_saves_a_franklin_insights_configuration(): void
    {
        $tokenString = 'gtuzfkjkqsoftkrugtjkfqfqmsldktumtuufj';
        $configuration = new Configuration();
        $configuration->setToken(new Token($tokenString));

        $this->getRepository()->save($configuration);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query('SELECT * FROM pim_configuration WHERE code = "franklin_token";');
        $retrievedConfiguration = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertSame('franklin_token', $retrievedConfiguration['code']);
        $this->assertSame([$tokenString], json_decode($retrievedConfiguration['values'], true));
    }

    public function test_it_updates_a_franklin_insights_configuration(): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token('a_first_token'));
        $this->getRepository()->save($configuration);

        $this->get('doctrine.orm.entity_manager')->clear();

        $configuration->setToken(new Token('a_new_token'));
        $this->getRepository()->save($configuration);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $statement = $entityManager->getConnection()->query('SELECT * FROM pim_configuration WHERE code = "franklin_token";');
        $retrievedConfiguration = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertSame('franklin_token', $retrievedConfiguration['code']);
        $this->assertSame(['a_new_token'], json_decode($retrievedConfiguration['values'], true));
    }

    public function test_it_finds_a_franklin_insights_configuration(): void
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

    public function test_it_removes_a_franklin_insights_configuration(): void
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
        return $this->get('akeneo.pim.automation.franklin_insights.repository.configuration');
    }
}
