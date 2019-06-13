<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;

class CalculateCompletenessCommandIntegration extends AbstractCompletenessTestCase
{
    public function testToCalculeCompletenessAfterPurge()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_text',
            AttributeTypes::TEXT,
            false,
            false
        );

        $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'juste un texte'
                        ],
                    ]
                ]
            ]
        );

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $commandLauncher = new CommandLauncher(static::$kernel);

        $exitCode = $commandLauncher->execute('pim:completeness:purge');
        $this->assertSame(0, $exitCode);
        $this->assertSame(0, $this->getCountRowCompleteness());

        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $exitCode = $commandLauncher->execute('pim:completeness:calculate');
        $this->assertSame(0, $exitCode);
        $this->assertSame(1, $this->getCountRowCompleteness());
    }

    private function getCountRowCompleteness()
    {
        $sql = 'SELECT count(*) AS count FROM pim_catalog_completeness';
        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->execute();

        return (int) $stmt->fetch()['count'];
    }
}
