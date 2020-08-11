<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AssociationType\Query;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Association\Sql\SqlGetAssociationTypeTranslations;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetAssociationTypeTranslationsIntegration extends TestCase
{
    public function test_it_gets_association_type_translations_by_giving_association_type_codes_and_locale_code(): void
    {
        $query = $this->getQuery();

        $this->givenAssociationTypes([
            [
                'code' => 'X_SELL_NEW',
                'labels' => [
                    'en_US' => 'cross sell',
                    'fr_FR' => 'vente croisée'
                ]
            ],
            [
                'code' => 'UP_SELL_NEW',
                'labels' => [
                    'en_US' => 'up sell',
                    'fr_FR' => 'vente incitative'
                ]
            ]
        ]);

        $expected = [
            'X_SELL_NEW' => 'vente croisée',
            'UP_SELL_NEW' => 'vente incitative',
        ];
        $actual = $query->byAssociationTypeCodeAndLocale(['X_SELL_NEW', 'UP_SELL_NEW'], 'fr_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetAssociationTypeTranslations
    {
        return $this->get('akeneo.pim.structure.query.get_association_type_translations');
    }

    private function givenAssociationTypes(array $associationTypes): void
    {
        $associationTypes = array_map(function (array $associationTypeData) {
            $associationType = $this->get('pim_catalog.factory.association_type')->create();
            $this->get('pim_catalog.updater.association_type')->update($associationType, $associationTypeData);
            $constraintViolations = $this->get('validator')->validate($associationType);

            Assert::count($constraintViolations, 0);

            return $associationType;
        }, $associationTypes);

        $this->get('pim_catalog.saver.association_type')->saveAll($associationTypes);
    }
}
