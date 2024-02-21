<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeGroup\Query;

use Akeneo\Pim\Structure\Bundle\Query\InternalAPI\AttributeGroup\Sql\FindAttributeCodesForAttributeGroup;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class SqlFindAttributeCodesForAttributeGroupIntegration extends TestCase
{
    /** @var Connection */
    private $connection;

    /** @var FindAttributeCodesForAttributeGroup */
    private $findAttributeCodesForAttributeGroup;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->findAttributeCodesForAttributeGroup = $this->get('akeneo.pim.structure.query.find_attribute_codes_for_attribute_groups');
    }

    public function testQueryToGetAssociatedProductCodes()
    {
        $attributeGroupCode = 'Marketing';
        $this->loadAttributesForAttributeGroup($attributeGroupCode);

        $actual = $this->findAttributeCodesForAttributeGroup->execute($attributeGroupCode);

        $this->assertAttributeCodesAreCorrect($actual);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadAttributesForAttributeGroup(string $attributeCode): void
    {
        $insertAttributeGroup = <<<SQL
INSERT INTO `pim_catalog_attribute_group` (`id`, `code`, `sort_order`, `created`, `updated`)
VALUES
	(29, :attribute_group_code, 0, '2017-10-09 12:23:59', '2017-12-14 11:36:48');
SQL;
        $insertAttributesForGroup = <<<SQL
        INSERT INTO `pim_catalog_attribute` (`group_id`, `sort_order`, `useable_as_grid_filter`, `max_characters`, `validation_rule`, `validation_regexp`, `wysiwyg_enabled`, `number_min`, `number_max`, `decimals_allowed`, `negative_allowed`, `date_min`, `date_max`, `metric_family`, `default_metric_unit`, `max_file_size`, `allowed_extensions`, `minimumInputLength`, `is_required`, `is_unique`, `is_localizable`, `is_scopable`, `code`, `entity_type`, `attribute_type`, `backend_type`, `properties`, `created`, `updated`)
VALUES
	(29, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 'ValidationAchat', 'Pim\\Component\\Catalog\\Model\\Product', 'pim_catalog_boolean', 'boolean', 'a:0:{}', '2019-03-07 16:39:40', '2019-03-07 16:39:40'),
	(29, 1, 1, NULL, 'regexp', '/^(EAN\\|[0-9]{13})|(UPC\\|[0-9]{12})|(ISBN\\|(97(8|9))?\\d{9}(\\d|X))|(SKU\\|[A-Z0-9a-z\\._-]{1,25}?)|(MPN\\|[A-Z0-9a-z]+)/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, 1, 1, 0, 0, 'sku', 'Pim\\Component\\Catalog\\Model\\Product', 'pim_catalog_identifier', 'text', 'a:3:{s:19:\"reference_data_name\";N;s:12:\"is_read_only\";b:1;s:19:\"auto_option_sorting\";N;}', '2017-06-27 10:10:23', '2019-03-07 16:40:48'),
	(29, 2, 1, NULL, 'regexp', '/^[0-9]{8}/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, 0, 1, 0, 0, 'Reference', 'Pim\\Component\\Catalog\\Model\\Product', 'pim_catalog_text', 'text', 'a:3:{s:12:\"is_read_only\";b:1;s:19:\"reference_data_name\";N;s:19:\"auto_option_sorting\";N;}', '2017-10-09 12:24:29', '2019-09-18 13:34:37'),
	(29, 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, 0, 0, 1, 0, 'Libelle', 'Pim\\Component\\Catalog\\Model\\Product', 'pim_catalog_text', 'text', 'a:3:{s:12:\"is_read_only\";b:1;s:19:\"reference_data_name\";N;s:19:\"auto_option_sorting\";N;}', '2017-10-09 12:24:29', '2019-03-07 16:40:48'),
	(29, 4, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, 0, 0, 0, 0, 'IsInternet', 'Pim\\Component\\Catalog\\Model\\Product', 'pim_catalog_multiselect', 'options', 'a:3:{s:12:\"is_read_only\";b:0;s:19:\"reference_data_name\";N;s:19:\"auto_option_sorting\";N;}', '2017-10-09 12:24:29', '2017-12-23 09:40:09'),
	(29, 5, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, 0, 0, 0, 0, 'VentilationNonOk', 'Pim\\Component\\Catalog\\Model\\Product', 'pim_catalog_boolean', 'boolean', 'a:3:{s:12:\"is_read_only\";b:0;s:19:\"reference_data_name\";N;s:19:\"auto_option_sorting\";N;}', '2017-10-09 12:24:29', '2017-12-19 13:02:01'),
	(29, 6, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 'InterditAuxMineurs', 'Pim\\Component\\Catalog\\Model\\Product', 'pim_catalog_boolean', 'boolean', 'a:0:{}', '2018-03-02 12:35:05', '2018-03-02 12:38:26'),
	(29, 7, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 'PieceDetachee', 'Pim\\Component\\Catalog\\Model\\Product', 'pim_catalog_boolean', 'boolean', 'a:0:{}', '2018-09-21 07:23:25', '2018-09-26 12:57:53');
SQL;

        $res = $this->connection->executeUpdate($insertAttributeGroup, ['attribute_group_code' => $attributeCode]);
        self::assertEquals(1, $res, 'Attribute group has not been inserted in DB correctly');
        $res = $this->connection->executeUpdate($insertAttributesForGroup);
        self::assertEquals(8, $res, 'Attributes have not been inserted in DB correctly');
    }

    private function assertAttributeCodesAreCorrect(array $actual): void
    {
        self::assertSame(
            [
                'ValidationAchat',
                'sku',
                'Reference',
                'Libelle',
                'IsInternet',
                'VentilationNonOk',
                'InterditAuxMineurs',
                'PieceDetachee',
            ],
            $actual
        );
    }
}
