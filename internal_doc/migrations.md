# About migrations and their tests

Going towards SaaS means being able to ship more often and in a more reliable way. Which means each code change *must* contain the related database, index or file storage migration(s). Two mechanisms are here to help us in this task:

- an automatic detection of missing *structure* migrations (when you change the structure of the database or the mapping of the Elasticsearch indices)
- the possibility to create tests for *data* migrations (for example, removing all null value in a the product values, or renaming the Versioning entity_name for product following a namespace change)

## About migrations

[Doctrine Migrations](https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html) is the tool we use to launch *all* our migrations. The command `doctrine:migrations:migrate` is called during the SaaS deployments. This is the *single entry point* for the ops to launch our dev migrations. So please, don't write anymore an independent PHP or bash script.

Migrations are located in the directory `upgrades/schema/`.

## Good practices

- Don't write a *down migration*. In case we need a rollback, it'll be handled directly by the ops with more powerful and reliable tools than what we could do by writing a *down migration*.
- In case of structure and data migrations on the same change, write 2 different migrations. That should help you write smaller and simpler tests.
- A migration is immutable: once your migration has been merged on master, it represents the past (and you can't alter the past).  For instance, imagine you changed a table with a migration `M1` which aims to go from `3.2` to `4.0`. `M1` is merged on master. Two weeks later you have to change again this table. You *must* write *another* migration `M2`. Indeed, our tests try to compare master, which already contains `M1`, (and not `3.2`) to your branch.

## Creating tests for data migrations

Data migration tests are integration tests. Therefore, we use `PHPUnit`. A dedicated suite `PIM_Migration_Test` will launch them in the job `test_back_data_migrations` of the CI.

Tests related to migrations are located in the directory `upgrades/test_schema`. The class name must be suffixed by `Integration`. Also, please keep the same name between the migration and its test. For instance, if you have a migration called `Version_4_0_20190916122240_foo`, its test should be called `Version_4_0_20190916122240_foo_Integration`.

**There is no way to automatically detect if a data migration is missing.** Therefore, it's the responsibility of each team to write them. Also, we strongly encourage you to write them in TDD mode to ensure your migrations does what they are supposed to do. 

Data migration tests are only valid during a limited period of time. For instance, all tests `Version_4_0_*_Integration` will no longer be useful once the PIM 4.0 is released. As such, they should be removed once necessary.

### Example of a database migration

Imagine the author of a `ProductDraft` should always be suffixed by "Joe". (Yes, that's stupid.)

The test could be like:

```php
<?php

namespace Pimee\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

class Version_4_0_20190916122240_foo_Integration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testAuthorEndsByJoe()
    {
        // arrange: create a draft with an author
        $this->get('database_connection')->executeQuery(<<<SQL
INSERT INTO pim_catalog_product
VALUES (NULL, NULL, NULL, NULL, 1, 'foo', '{}', NOW(), NOW());
INSERT INTO pimee_workflow_product_draft 
VALUES (NULL, 1, NOW(), '{}', '{}', 0, '', '', 'Ian Murdock', '');
SQL);

        // act: execute our migration
        $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel())
        );

        // assert: the author should now be named 'Ian MurdockJoe'
        $this->assertEquals(
            'Ian MurdockJoe',
            $this->get('database_connection')->fetchOne('SELECT DISTINCT author FROM pimee_workflow_product_draft')
        );
    }

    private function getMigrationLabel()
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
```

The related migration would be:

```php
<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_4_0_20190916122240_foo extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
            UPDATE pimee_workflow_product_draft
            SET author = CONCAT(author, 'Joe')
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
```

### Example of an index migration

Imagine the author of a `ProductDraft` should always be suffixed by "Joe". (Yes, that's still stupid.)

The test could be like:

```php
<?php

namespace Pimee\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

class Version_4_0_20190916122290_bar_Integration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testAuthorEndsByJoe()
    {
        $client = $this->get('akeneo_elasticsearch.client.product_proposal');

        // arrange: create a draft with an author
        $client->index(
            'a_draft',
            ['author' => 'Ian Murdock']
        );

        $client->refreshIndex();

        // act: execute our migration
        $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel())
        );

        $client->refreshIndex();

        // assert: the author should now be named 'Ian MurdockJoe'
        $draft = $client->get('a_draft');
        $this->assertEquals('Ian MurdockJoe', $draft['_source']['author']);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
```

The related migration would be:

```php
<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_4_0_20190916122290_bar extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function up(Schema $schema): void
    {
        $client = $this->container->get('akeneo_elasticsearch.client.product_proposal');
        $client->updateByQuery(
            [
                "script" => [
                    "source" => "ctx._source.author = ctx._source.author + 'Joe'",
                    "lang"   => "painless"
                ]
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
```

## Automatic detection of missing structure migrations

Database structure changes and index mapping changes are automatically detected by the script `vendor/akeneo/pim-community-dev/.circleci/detect_structure_changes.sh` that is launched by the job `test_back_missing_structure_migrations` on the CI.

The process is quite simple:

1. apply branch migrations on top of master database and indices
2. install branch database and indices from scratch
3. compare the results of the two first steps

In case a difference is spotted, the script will exit with a non zero status, and the CI will be red. That means you forgot a migration in your pull request :)

As structure migrations are automatically detected, there is no need to add an integration test on it. 
