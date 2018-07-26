<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\Domain\Query\FindEnrichedEntityDetailsInterface;
use Akeneo\EnrichedEntity\tests\back\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlFindEnrichedEntityDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindEnrichedEntityDetailsInterface */
    private $findEnrichedEntityDetails;

    public function setUp()
    {
        parent::setUp();

        $this->findEnrichedEntityDetails = $this->get('akeneo_enrichedentity.infrastructure.persistence.query.find_enriched_entity_details');
        $this->resetDB();
        $this->loadEnrichedEntity();
    }

    /**
     * @test
     */
    public function it_returns_null_when_there_is_no_result_for_the_given_identifier()
    {
        $result = ($this->findEnrichedEntityDetails)(EnrichedEntityIdentifier::fromString('unknown_enriched_entity'));
        Assert::assertNull($result);
    }

    /**
     * @test
     */
    public function it_finds_one_enriched_entity_by_its_identifier()
    {
        $entity = ($this->findEnrichedEntityDetails)(EnrichedEntityIdentifier::fromString('designer'));

        $designer = new EnrichedEntityDetails();
        $designer->identifier = EnrichedEntityIdentifier::fromString('designer');
        $designer->labels = LabelCollection::fromArray(['fr_FR' => 'Concepteur', 'en_US' => 'Designer']);

        $this->assertEnrichedEntityItem($designer, $entity);
    }

    private function resetDB(): void
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_enriched_entity;
SQL;

        $this->get('database_connection')->executeQuery($resetQuery);
    }

    private function loadEnrichedEntity(): void
    {
        $enrichedEntityRepository = $this->get('akeneo_enrichedentity.infrastructure.persistence.enriched_entity');
        $enrichedEntity = EnrichedEntity::create(
            EnrichedEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ]
        );
        $enrichedEntityRepository->create($enrichedEntity);
    }

    private function assertEnrichedEntityItem(EnrichedEntityDetails $expected, EnrichedEntityDetails $actual): void
    {
        $this->assertTrue($expected->identifier->equals($actual->identifier), 'Enriched entity identifiers are not equal');
        $expectedLabels = $this->normalizeLabels($expected->labels);
        $actualLabels = $this->normalizeLabels($actual->labels);
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            ),
            'Labels for the enriched entity items are not the same'
        );
    }

    private function normalizeLabels(LabelCollection $labelCollection): array
    {
        $labels = [];
        foreach ($labelCollection->getLocaleCodes() as $localeCode) {
            $labels[$localeCode] = $labelCollection->getLabel($localeCode);
        }

        return $labels;
    }
}
