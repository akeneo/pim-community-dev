<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantSearchableRepositoryIntegration extends TestCase
{
    public function test_it_searches_family_variants_by_code()
    {
        static::assertCount(1, $this->searchFamilyVariant('by_size'));
        static::assertCount(1, $this->searchFamilyVariant('by_color'));
    }

    public function it_searches_family_variants_by_label()
    {
        static::assertCount(2, $this->searchFamilyVariant('By'));
        static::assertCount(2, $this->searchFamilyVariant('par'));
        static::assertCount(1, $this->searchFamilyVariant('color'));
        static::assertCount(1, $this->searchFamilyVariant('taille'));
        static::assertCount(0, $this->searchFamilyVariant('unexisting'));
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

        $defaultGroup = $this->get('pim_catalog.repository.attribute_group')->findDefaultAttributeGroup();
        $this->createAttribute(
            [
                'code' => 'size',
                'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
                'group' => $defaultGroup->getCode(),
            ]
        );

        $this->createAttribute(
            [
                'code' => 'color',
                'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
                'group' => $defaultGroup->getCode(),
            ]
        );
        $this->createFamily(
            [
                'code' => 'clothing',
                'labels' => [
                    'en_US' => 'Clothes',
                    'fr_FR' => 'Vêtements',
                ],
                'attributes' => [
                    'sku',
                    'size',
                    'color',
                ],
            ]
        );
        $this->createFamilyVariant(
            [
                'family' => 'clothing',
                'code' => 'by_size',
                'labels' => [
                    'en_US' => 'By size',
                    'fr_FR' => 'Par taille',
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['size'],
                        'attributes' => [],
                        'level' => 1,
                    ],

                ],
            ]
        );
        $this->createFamilyVariant(
            [
                'family' => 'clothing',
                'code' => 'by_color',
                'labels' => [
                    'en_US' => 'By color',
                    'fr_FR' => 'Par couleur',
                ],
                'variant_attribute_sets' => [
                    [
                        'axes' => ['color'],
                        'attributes' => [],
                        'level' => 1,
                    ],

                ],
            ]
        );
    }

    /**
     * @param array $data
     */
    private function createAttribute(array $data): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * @param array $data
     */
    private function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * @param array $data
     */
    private function createFamilyVariant(array $data): void
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);
    }

    /**
     * @param string $search
     *
     * @return FamilyVariantInterface[]
     */
    private function searchFamilyVariant(string $search): array
    {
        return $this->get('pim_enrich.repository.family_variant.search')->findBySearch($search);
    }
}
