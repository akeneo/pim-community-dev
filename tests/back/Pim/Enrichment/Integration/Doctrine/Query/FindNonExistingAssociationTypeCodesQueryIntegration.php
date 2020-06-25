<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingAssociationTypeCodesQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Test\Integration\TestCase;

class FindNonExistingAssociationTypeCodesQueryIntegration extends TestCase
{
    /** @var FindNonExistingAssociationTypeCodesQueryInterface */
    private $findNonExistingAssociationTypeCodesQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findNonExistingAssociationTypeCodesQuery = $this->get(
            'akeneo.pim.enrichment.product.query.find_non_existing_association_type_codes_query'
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     */
    public function it_return_nothing_when_nothing_passed()
    {
        self::assertEquals([], $this->findNonExistingAssociationTypeCodesQuery->execute([]));
    }

    /**
     * @test
     */
    public function it_returns_the_association_type_codes_that_does_not_exists()
    {
        $existingAssociationTypes = [
            'association_type_1',
            'association_type_2',
            'association_type_3',
            'association_type_4',
            'association_type_5',
        ];

        foreach ($existingAssociationTypes as $existingAssociationType) {
            $this->createQuantifiedAssociationType($existingAssociationType);
        }

        $lookupCodes = [
            'association_type_1',
            'association_type_2',
            'association_type_3',
            'association_type_does_not_exists',
        ];

        $actualNonExistingCodes = $this->findNonExistingAssociationTypeCodesQuery->execute(
            $lookupCodes
        );
        $expectedNonExistingCodes = [
            'association_type_does_not_exists',
        ];

        self::assertEquals(
            $actualNonExistingCodes,
            $expectedNonExistingCodes
        );
    }

    protected function createQuantifiedAssociationType(string $code): AssociationType
    {
        $factory = $this->get('pim_catalog.factory.association_type');
        $updater = $this->get('pim_catalog.updater.association_type');
        $saver = $this->get('pim_catalog.saver.association_type');

        $associationType = $factory->create();
        $updater->update($associationType, ['code' => $code, 'is_quantified' => true]);
        $saver->save($associationType);

        return $associationType;
    }
}
