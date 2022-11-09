<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version_6_0_20210191103602_dqi_init_default_dictionary_words extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema) : void
    {
        $defaultWords = [
            'sku', 'upc', 'asin', 'ean', 'mpn', 'gtin', 'jan', 'isbn', 'erp',
            'xxs', 'xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl',
        ];

        $query = <<<SQL
SELECT code FROM pim_catalog_locale WHERE is_activated = 1;
SQL;
        $stmt = $this->container->get('database_connection')->executeQuery($query);

        while ($locale = $stmt->fetchColumn()) {
            $values = array_map(fn (string $word) => sprintf("('%s', '%s')", $locale, $word), $defaultWords);

            $this->addSql(sprintf(
                'INSERT IGNORE INTO pimee_data_quality_insights_text_checker_dictionary (locale_code, word) VALUES %s',
                implode(',', $values)));
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
