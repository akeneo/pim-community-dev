<?php

declare(strict_types=1);

namespace tests\integration\Pim\Bundle\CatalogBundle\Completeness;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\CatalogBundle\tests\integration\Completeness\AbstractCompletenessTestCase;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Checks the completeness is computed whenever the required attributes of a family is changed.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateCompletenessOnFamilyUpdateIntegration extends AbstractCompletenessTestCase
{
    public function testComputeCompletenessForProductWhenUpdatingAttributeRequirements()
    {
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 20);
        $this->addFamilyRequirement('accessories', 'ecommerce', 'color');
        $this->assertCompleteness('watch', 'ecommerce', 'fr_FR', 33);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }

    /**
     * @param $productIdentifier
     * @param $channelCode
     * @param $localeCode
     * @param $ratio
     */
    private function assertCompleteness($productIdentifier, $channelCode, $localeCode, $ratio): void
    {
        $this->get('doctrine.orm.default_entity_manager')->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);
        $completeness = $this->getCompletenesses($product, $channelCode, $localeCode);

        $this->assertNotNull($completeness);
        $this->assertEquals($ratio, $completeness->getRatio());
    }

    /**
     * Return the completeness of a product for a channel and a locale.
     *
     * @param ProductInterface $product
     * @param string           $channelCode
     * @param string           $localeCode
     *
     * @return CompletenessInterface
     */
    private function getCompletenesses(
        ProductInterface $product,
        string $channelCode,
        string $localeCode
    ): ?CompletenessInterface {
        $completenesses = $product->getCompletenesses();

        foreach ($completenesses as $completeness) {
            if ($channelCode === $completeness->getChannel()->getCode() &&
                $localeCode === $completeness->getLocale()->getCode()) {
                return $completeness;
            }
        }

        return null;
    }
}
