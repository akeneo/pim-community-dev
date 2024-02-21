<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

class Version_7_0_20220330095652_ensure_locale_codes_for_labels_in_channels_have_correct_case_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220330095652_ensure_locale_codes_for_labels_in_channels_have_correct_case';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function makeTestChannel()
    {
        $cnx = $this->get('database_connection');

        $result = $cnx->fetchNumeric('SELECT id FROM pim_catalog_category WHERE code = ?', ['master']);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $master_category_id = $result[0];


        $result = $cnx->insert('pim_catalog_channel', [
            'category_id' => $master_category_id,
            'code' => 'test channel',
            'conversionUnits' => 'a:0:{}'
        ]);
        $this->assertTrue($result === 1);
        $channel_id = $cnx->lastInsertId();


        $result = $cnx->insert('pim_catalog_channel_translation', [
            'foreign_key' => $channel_id,
            'label' => 'test channel fr_FR',
            'locale' => 'fr_FR' // correspond to some locale in pim_catalog_locale, with same case
        ]);
        $this->assertTrue($result === 1);

        $result = $cnx->insert('pim_catalog_channel_translation', [
            'foreign_key' => $channel_id,
            'label' => 'test channel en_US',
            'locale' => 'EN_US' // correspond to some locale in pim_catalog_locale, but wrong case
        ]);
        $this->assertTrue($result === 1);

        $result = $cnx->insert('pim_catalog_channel_translation', [
            'foreign_key' => $channel_id,
            'label' => 'test channel foo_bar',
            'locale' => 'foo_bar' // does not correspond to some locale in pim_catalog_locale
        ]);
        $this->assertTrue($result === 1);

        return $channel_id;
    }

    public function test_known_locale_codes_are_normalized_for_channel_labels(): void
    {

        $channel_id = $this->makeTestChannel();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $cnx = $this->get('database_connection');

        $localeCodes = $cnx->fetchAllAssociative('SELECT locale FROM pim_catalog_channel_translation WHERE foreign_key=? ORDER BY locale ASC', [$channel_id]);

        $this->assertEquals($localeCodes, [['locale' => 'en_US'], ['locale' => 'foo_bar'], ['locale' => 'fr_FR']]);
    }
}
