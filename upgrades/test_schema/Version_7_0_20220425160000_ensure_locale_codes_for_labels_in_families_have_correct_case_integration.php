<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;

class Version_7_0_20220425160000_ensure_locale_codes_for_labels_in_families_have_correct_case_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220425160000_ensure_locale_codes_for_labels_in_families_have_correct_case';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function makeTestFamily()
    {
        $cnx = $this->get('database_connection');

        $result = $cnx->insert('pim_catalog_family', [
            'code' => 'test family',
            'created' => (new \DateTime())->format('Y-m-d H:i:s'),
            'updated' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
        $this->assertTrue($result === 1);
        $familyId = $cnx->lastInsertId();

        $result = $cnx->insert('pim_catalog_family_translation', [
            'foreign_key' => $familyId,
            'label' => 'test family fr_FR',
            'locale' => 'fr_FR' // correspond to some locale in pim_catalog_locale, with same case
        ]);
        $this->assertTrue($result === 1);

        $result = $cnx->insert('pim_catalog_family_translation', [
            'foreign_key' => $familyId,
            'label' => 'test family en_US',
            'locale' => 'en_us' // correspond to some locale in pim_catalog_locale, but wrong case
        ]);
        $this->assertTrue($result === 1);

        $result = $cnx->insert('pim_catalog_family_translation', [
            'foreign_key' => $familyId,
            'label' => 'test family foo_bar',
            'locale' => 'foo_bar' // does not correspond to some locale in pim_catalog_locale
        ]);
        $this->assertTrue($result === 1);

        return $familyId;
    }

    public function test_known_locale_codes_are_normalized_for_family_labels(): void
    {

        $familyId = $this->makeTestFamily();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $cnx = $this->get('database_connection');

        $localeCodes = $cnx->fetchFirstColumn('SELECT locale FROM pim_catalog_family_translation WHERE foreign_key=? ORDER BY locale ASC', [$familyId]);

        $this->assertEquals($localeCodes, ['en_US', 'foo_bar', 'fr_FR']);
    }
}
