<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AssociationType\Query;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Association\Sql\SqlGetAssociationTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\AssociationType;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\LabelCollection;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetAssociationTypesIntegration extends TestCase
{
    public function test_it_gets_association_type_indexed_by_association_type_codes(): void
    {
        $this->givenAssociationTypes([
            [
                'code' => 'MY_X_SELL',
                'labels' => [
                    'en_US' => 'cross sell',
                    'fr_FR' => 'vente croisée'
                ],
            ],
            [
                'code' => 'MY_UP_SELL',
                'labels' => [
                    'en_US' => 'up sell',
                    'fr_FR' => 'vente incitative'
                ],
            ],
            [
                'code' => 'MY_PACK',
                'labels' => [],
                'is_quantified' => true,
            ],
            [
                'code' => 'MY_COMPATIBLE',
                'labels' => [
                    'en_US' => 'Compatible'
                ],
                'is_two_way' => true,
            ],
        ]);

        $expected = [
            'MY_X_SELL' => new AssociationType(
                'MY_X_SELL',
                LabelCollection::fromArray(['en_US' => 'cross sell', 'fr_FR' => 'vente croisée']),
                false,
                false
            ),
            'MY_PACK' => new AssociationType(
                'MY_PACK',
                LabelCollection::fromArray([]),
                false,
                true
            ),
            'MY_COMPATIBLE' => new AssociationType(
                'MY_COMPATIBLE',
                LabelCollection::fromArray(['en_US' => 'Compatible']),
                true,
                false
            ),
        ];

        $actual = $this->getQuery()->forCodes(['MY_X_SELL', 'MY_PACK', 'MY_COMPATIBLE']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetAssociationTypes
    {
        return $this->get('akeneo.pim.structure.query.get_association_types');
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
