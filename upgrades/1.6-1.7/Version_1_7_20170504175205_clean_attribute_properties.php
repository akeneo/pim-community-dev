<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Upgrade\SchemaHelper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version_1_7_20170504175205_clean_attribute_properties extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        $tableHelper = new SchemaHelper($this->container);
        $attributeTable = $tableHelper->getTableOrCollection('attribute');

        $this->addSql(
            sprintf('UPDATE %s SET max_characters = NULL WHERE attribute_type NOT IN (?, ?, ?)', $attributeTable),
            [AttributeTypes::IDENTIFIER, AttributeTypes::TEXT, AttributeTypes::TEXTAREA]
        );

        $this->addSql(
            sprintf('UPDATE %s SET validation_rule = NULL WHERE attribute_type NOT IN (?, ?)', $attributeTable),
            [AttributeTypes::IDENTIFIER, AttributeTypes::TEXT]
        );

        $this->addSql(
            sprintf('UPDATE %s SET validation_regexp = NULL WHERE attribute_type NOT IN (?, ?)', $attributeTable),
            [AttributeTypes::IDENTIFIER, AttributeTypes::TEXT]
        );

        $this->addSql(
            sprintf('UPDATE %s SET wysiwyg_enabled = NULL WHERE attribute_type != ?', $attributeTable),
            [AttributeTypes::TEXTAREA]
        );

        $this->addSql(
            sprintf('UPDATE %s SET decimals_allowed = NULL WHERE attribute_type NOT IN (?, ?, ?)', $attributeTable),
            [AttributeTypes::NUMBER, AttributeTypes::METRIC, AttributeTypes::PRICE_COLLECTION]
        );

        $this->addSql(
            sprintf('UPDATE %s SET negative_allowed = NULL WHERE attribute_type NOT IN (?, ?)', $attributeTable),
            [AttributeTypes::NUMBER, AttributeTypes::METRIC]
        );
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
