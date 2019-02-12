<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySearchableRepositoryIntegration extends TestCase
{
    public function test_it_searches_families_by_code()
    {
        static::assertCount(1, $this->searchFamily('clothing'));
        static::assertCount(1, $this->searchFamily('other'));
    }

    public function test_it_searches_families_by_label()
    {
        static::assertCount(2, $this->searchFamily('vetem'));
        static::assertCount(2, $this->searchFamily('clothes'));
        static::assertCount(1, $this->searchFamily('alter'));
        static::assertCount(1, $this->searchFamily('autre'));
        static::assertCount(0, $this->searchFamily('unexisting'));
    }

    public function test_it_searches_families_with_limit_option()
    {
        static::assertCount(3, $this->searchFamily('c', ['limit' => 3, 'page' => 1]));
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->initFixtures();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
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

    /**
     * @param $data
     */
    private function createFamily($data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);

        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * @param string $search
     *
     * @return FamilyInterface[]
     */
    private function searchFamily(string $search, array $options = []): array
    {
        return $this->get('pim_enrich.repository.family.search')->findBySearch($search,$options);
    }
}
