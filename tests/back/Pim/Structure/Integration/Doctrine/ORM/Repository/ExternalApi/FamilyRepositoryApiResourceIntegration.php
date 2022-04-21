<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Bundle\Doctrine\ORM\Repository\ExternalApi;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\ExternalApi\FamilyRepository;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class FamilyRepositoryApiResourceIntegration extends TestCase
{
    public function test_to_get_the_identifier_properties(): void
    {
        $properties = $this->getRepository()->getIdentifierProperties();
        Assert::assertEquals(['code'], $properties);
    }

    public function test_to_find_a_family_by_code(): void
    {
        $this->initFixtures();
        $family = $this->getRepository()->findOneByIdentifier('accessories');

        Assert::assertInstanceOf(FamilyInterface::class, $family);
        Assert::assertEquals('accessories', $family->getCode());
    }

    public function test_to_get_families(): void
    {
        $this->initFixtures();
        $families = $this->getRepository()->searchAfterOffset([], [], 10, 0);
        Assert::assertCount(3, $families);
    }

    public function test_to_count_families(): void
    {
        $this->initFixtures();
        $count = $this->getRepository()->count([]);
        Assert::assertEquals(3, $count);
    }

    public function test_to_count_families_with_search(): void
    {
        $this->initFixtures();
        $count = $this->getRepository()->count(['code' => [['operator' => 'IN', 'value' => ['accessories', 'other']]]]);
        Assert::assertEquals(2, $count);
    }

    public function test_to_get_families_with_limit(): void
    {
        $this->initFixtures();
        $families = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 2, 0);
        Assert::assertCount(2, $families);
        Assert::assertEquals('accessories', $families[0]->getCode());
        Assert::assertEquals('clothing', $families[1]->getCode());
    }

    public function test_to_search_families_after_the_offset(): void
    {
        $this->initFixtures();
        $families = $this->getRepository()->searchAfterOffset([], ['code' => 'ASC'], 2, 2);
        Assert::assertCount(1, $families);
        Assert::assertEquals('other', $families[0]->getCode());
    }

    public function test_to_search_ordered_families(): void
    {
        $this->initFixtures();
        $this->updateDatetime();

        $familiesCodeDesc = $this->getRepository()->searchAfterOffset([], ['code' => 'DESC'], 3, 0);
        Assert::assertCount(3, $familiesCodeDesc);
        Assert::assertEquals('other', $familiesCodeDesc[0]->getCode());
        Assert::assertEquals('clothing', $familiesCodeDesc[1]->getCode());
        Assert::assertEquals('accessories', $familiesCodeDesc[2]->getCode());

        $familiesCodeAsc = $this->getRepository()->searchAfterOffset([], ['updated' => 'DESC'], 3, 0);
        Assert::assertCount(3, $familiesCodeAsc);
        Assert::assertEquals('other', $familiesCodeAsc[0]->getCode());
        Assert::assertEquals('accessories', $familiesCodeAsc[1]->getCode());
        Assert::assertEquals('clothing', $familiesCodeAsc[2]->getCode());
    }

    public function test_to_search_families_by_codes(): void
    {
        $this->initFixtures();

        $families = $this->getRepository()->searchAfterOffset(
            ['code' => [['operator' => 'IN', 'value' => ['accessories', 'other']]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(2, $families);
        Assert::assertEquals('accessories', $families[0]->getCode());
        Assert::assertEquals('other', $families[1]->getCode());
    }

    public function test_to_search_families_by_updated_date(): void
    {
        $this->initFixtures();
        $this->updateDatetime();

        $since2019 = new \DateTime('2019-01-01 00:00:00', new \DateTimeZone('UTC'));
        $since2019Families = $this->getRepository()->searchAfterOffset(
            ['updated' => [['operator' => '>', 'value' => $since2019->format(DATE_ATOM)]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(2, $since2019Families);
        Assert::assertEquals('accessories', $since2019Families[0]->getCode());
        Assert::assertEquals('other', $since2019Families[1]->getCode());

        $since2020 = new \DateTime('2020-01-01 00:00:00', new \DateTimeZone('UTC'));
        $since2020Families = $this->getRepository()->searchAfterOffset(
            ['updated' => [['operator' => '>', 'value' => $since2020->format(DATE_ATOM)]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(1, $since2020Families);
        Assert::assertEquals('other', $since2020Families[0]->getCode());
    }

    public function test_to_search_families_with_products(): void
    {
        $this->createFamily(['code' => 'accessories']);
        $this->createFamily(['code' => 'clothing']);
        $this->createFamily(['code' => 'other']);
        $this->createProduct('a_product_with_a_family', ['family' => 'accessories']);
        $this->createProduct('another_product_with_a_family', ['family' => 'clothing',]);

        $families = $this->getRepository()->searchAfterOffset(
            ['has_products' => [['operator' => '=', 'value' => true]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(2, $families);
        Assert::assertEquals('accessories', $families[0]->getCode());
        Assert::assertEquals('clothing', $families[1]->getCode());
    }

    public function test_to_search_families_with_no_products(): void
    {
        $this->createFamily(['code' => 'accessories']);
        $this->createFamily(['code' => 'clothing']);
        $this->createFamily(['code' => 'other']);
        $this->createProduct('a_product_with_a_family', ['family' => 'accessories']);
        $this->createProduct('another_product_with_a_family', ['family' => 'clothing',]);

        $families = $this->getRepository()->searchAfterOffset(
            ['has_products' => [['operator' => '=', 'value' => false]]],
            ['code' => 'ASC'],
            5,
            0
        );
        Assert::assertCount(1, $families);
        Assert::assertEquals('other', $families[0]->getCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getRepository(): FamilyRepository
    {
        return $this->get('pim_api.repository.family');
    }

    private function updateDatetime(): void
    {
        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $query = <<<SQL
UPDATE pim_catalog_family
SET updated= :datetime
WHERE code = :code
SQL;
        $connection->executeUpdate(
            $query,
            [
                'code' => 'clothing',
                'datetime' => '2018-01-01 16:27:00',
            ]
        );
        $connection->executeUpdate(
            $query,
            [
                'code' => 'accessories',
                'datetime' => '2019-05-15 16:27:00',
            ]
        );
        $connection->executeUpdate(
            $query,
            [
                'code' => 'other',
                'datetime' => '2020-01-01 16:27:00',
            ]
        );
    }

    private function initFixtures(): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier('fr_FR');
        $channel->addLocale($locale);
        $this->get('pim_catalog.saver.channel')->save($channel);

        $this->createFamily(
            [
                'code' => 'accessories',
                'labels' => [
                    'en_US' => 'Accessories',
                    'fr_FR' => 'Accessories',
                    'fr_BE' => 'Accessories',
                ],
            ]
        );
        $this->createFamily(
            [
                'code' => 'clothing',
                'labels' => [
                    'en_US' => 'Clothes',
                    'fr_FR' => 'Vêtements',
                ],
            ]
        );
        $this->createFamily(
            [
                'code' => 'other',
                'labels' => [
                    'en_US' => 'Alternative clothes',
                    'fr_FR' => 'Autres vêtements',
                ],
            ]
        );
    }

    private function createFamily($data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);

        $this->get('pim_catalog.saver.family')->save($family);
    }
    private function createProduct(string $identifier, array $data): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
    }
}
