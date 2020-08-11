<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Group;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Group\Sql\SqlGetGroupTranslations;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetGroupTranslationsIntegration extends TestCase
{
    public function test_it_gets_group_translations_by_giving_group_codes_and_locale_code(): void
    {
        $query = $this->getQuery();

        $this->givenGroups([
            [
                'code' => 'new_group',
                'type'   => 'RELATED',
                'labels' => [
                    'en_US' => 'new group',
                    'fr_FR' => 'nouveau groupe'
                ]
            ],
            [
                'code' => 'new_group_again',
                'type'   => 'RELATED',
                'labels' => [
                    'en_US' => 'new group again',
                    'fr_FR' => 'nouveau groupe encore'
                ]
            ]
        ]);

        $expected = [
            'new_group_again' => 'nouveau groupe encore',
            'new_group' => 'nouveau groupe',
        ];
        $actual = $query->byGroupCodesAndLocale(['new_group', 'new_group_again'], 'fr_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetGroupTranslations
    {
        return $this->get('akeneo.pim.structure.query.get_group_translations');
    }

    private function givenGroups(array $families): void
    {
        $families = array_map(function (array $groupData) {
            $group = $this->get('pim_catalog.factory.group')->create();
            $this->get('pim_catalog.updater.group')->update($group, $groupData);
            $constraintViolations = $this->get('validator')->validate($group);

            Assert::count($constraintViolations, 0);

            return $group;
        }, $families);

        $this->get('pim_catalog.saver.group')->saveAll($families);
    }
}
