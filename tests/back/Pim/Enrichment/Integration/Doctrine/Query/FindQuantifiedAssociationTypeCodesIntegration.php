<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\FindQuantifiedAssociationTypeCodes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;

class FindQuantifiedAssociationTypeCodesIntegration extends TestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /** @var FindQuantifiedAssociationTypeCodes */
    private $findQuantifiedAssociationCodes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findQuantifiedAssociationCodes = $this->get(
            'akeneo.pim.enrichment.product.query.find_quantified_association_codes'
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     */
    public function it_return_nothing_when_no_quantified_association_type()
    {
        self::assertEquals([], $this->findQuantifiedAssociationCodes->execute());
    }

    /**
     * @test
     */
    public function it_returns_the_association_type_codes_that_does_not_exists()
    {
        $quantifiedAssociationTypes = [
            'association_type_1',
            'association_type_2',
            'association_type_3',
            'association_type_4',
            'association_type_5',
        ];

        foreach ($quantifiedAssociationTypes as $quantifiedAssociationType) {
            $this->createQuantifiedAssociationType($quantifiedAssociationType);
        }

        $actualQuantifiedAssociationTypeCodes = $this->findQuantifiedAssociationCodes->execute();

        self::assertEquals(
            $actualQuantifiedAssociationTypeCodes,
            $quantifiedAssociationTypes
        );
    }

    /** @test */
    public function it_caches_the_results()
    {
        $this->createQuantifiedAssociationType('association_type_1');
        $actualQuantifiedAssociationTypeCodes = $this->findQuantifiedAssociationCodes->execute(); // Cache is initialized
        self::assertEquals($actualQuantifiedAssociationTypeCodes, ['association_type_1']);

        $this->createQuantifiedAssociationType('association_type_2');
        $actualQuantifiedAssociationTypeCodes = $this->findQuantifiedAssociationCodes->execute(); // result is still the cache
        self::assertEquals($actualQuantifiedAssociationTypeCodes, ['association_type_1']);

        $this->findQuantifiedAssociationCodes->clearCache();
        $actualQuantifiedAssociationTypeCodes = $this->findQuantifiedAssociationCodes->execute(); // Cache is reinitialized
        self::assertEquals($actualQuantifiedAssociationTypeCodes, ['association_type_1', 'association_type_2']);
    }
}
