<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelIdentifierOrUuidFilterIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * We search labels and identifiers both on products and product models and
     * check that we get both in the same result
     */
    public function testSearch()
    {
        $result = $this->executeFilter([['label_identifier_or_uuid', Operators::CONTAINS, 'hat', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['model-braided-hat', '1111111240', 'braided-hat-m', 'braided-hat-xxxl']);

        $result = $this->executeFilter([['label_identifier_or_uuid', Operators::CONTAINS, 'ha', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, [
            'model-braided-hat',
            'hades',
            'braided-hat-m',
            'braided-hat-xxxl',
            '1111111234',
            '1111111235',
            '1111111236',
            '1111111237',
            '1111111238',
            '1111111239',
            '1111111240',
            'hades_blue',
            'hades_red',
        ]);
    }

    public function testSearchOnLabelAndCompleteness()
    {
        $result = $this->executeFilter([
            ['label_identifier_or_uuid', Operators::CONTAINS, 'hat', ['locale' => 'en_US', 'scope' => 'ecommerce']],
            ['completeness', Operators::AT_LEAST_COMPLETE, null, ['locale' => 'en_US', 'scope' => 'ecommerce']]
        ]);
        $this->assert($result, ['model-braided-hat', 'braided-hat-m', 'braided-hat-xxxl']);
    }

    public function testSearchWithUuid(): void
    {
        $uuid = $this->getUuidFromIdentifier('1111111240');

        $result = $this->executeFilter([
            ['label_identifier_or_uuid', Operators::CONTAINS, $uuid, ['locale' => 'en_US', 'scope' => 'ecommerce']],
        ]);

        $this->assertUuid($result, [$uuid]);
    }

    protected function getUuidFromIdentifier(string $identifier): string
    {
        $query = <<<SQL
            SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = :identifier
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['identifier' => $identifier]);
        $uuid = $stmt->fetchOne();
        if (null === $uuid) {
            throw new \InvalidArgumentException(\sprintf('No product exists with identifier "%s"', $identifier));
        }
        return $uuid;
    }
}
