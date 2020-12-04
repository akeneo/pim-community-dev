<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Sql\SqlGetFamilyTranslations;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class GetAllFamiliesLabelByLocaleQueryIntegration extends TestCase
{
    public function test_it_gets_family_translations_by_giving_family_codes_and_locale_code(): void
    {
        $query = $this->get('akeneo.pim.structure.query.get_all_families_label_by_locale');
        $results = $query->execute('en_US');

        $this->assertSame([
            'familyA2' => '[familyA2]',
            'familyA3' => '[familyA3]',
            'familyA' => 'A family A',
            'familyA1' => 'A family A1',
        ], $results);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
