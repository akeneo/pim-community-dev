<?php

declare(strict_types=1);

namespace tests\integration\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Model\FamilyVariantInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveFamilyVariantIntegration extends TestCase
{
    public function testTheFamilyVariantRemovalSuccess(): void
    {
        $familyVariant = $this->createDefaultFamilyVariant('my_family_variant');
        $this->removeFamilyVariant($familyVariant);

        $this->assertNull($this->getFamilyVariant('my_family_variant'));
    }

    public function testTheFamilyVariantRemovalIsPrevented()
    {
        $this->expectException(\LogicException::class);
        $familyVariant = $this->getFamilyVariant('shoes_size');
        $this->get('pim_catalog.remover.family_variant')->remove($familyVariant);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('footwear');
    }

    /**
     * Create a family variant with the code family_variant
     *
     * @param string $code
     *
     * @return FamilyVariantInterface
     */
    private function createDefaultFamilyVariant(string $code): FamilyVariantInterface
    {
        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();

        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, [
            'code'                   => $code,
            'family'                 => 'boots',
            'labels'                 => [
                'en_US' => 'My family variant',
            ],
            'variant_attribute_sets' => [
                [
                    'axes'       => ['color'],
                    'attributes' => ['weather_conditions', 'rating', 'side_view', 'top_view', 'lace_color'],
                    'level'      => 1,
                ],
                [
                    'axes'       => ['size'],
                    'attributes' => ['sku', 'price'],
                    'level'      => 2,
                ],
            ],
        ]);

        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    /**
     * @param string $code
     *
     * @return null|FamilyVariantInterface
     */
    private function getFamilyVariant(string $code): ?FamilyVariantInterface
    {
        return $this->get('pim_catalog.repository.family_variant')->findOneByIdentifier($code);
    }

    /**
     * @param $familyVariant
     */
    private function removeFamilyVariant(FamilyVariantInterface $familyVariant): void
    {
        $this->get('pim_catalog.remover.family_variant')->remove($familyVariant);
    }
}
