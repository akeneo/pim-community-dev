<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\SqlGetReferenceEntityIdentifierForRecordIdentifier;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlGetReferenceEntityIdentifierForRecordIdentifierTest extends SqlIntegrationTestCase
{
    /** @var SqlGetReferenceEntityIdentifierForRecordIdentifier */
    private $getReferenceEntityIdentifierForRecordIdentifier;

    public function setUp()
    {
        parent::setUp();

        $this->getReferenceEntityIdentifierForRecordIdentifier = $this->get('akeneo_referenceentity.infrastructure.persistence.query.get_reference_entity_identifier_for_record_identifier');
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
        $this->loadReferenceEntityAndAttributes();
    }

    /**
     * @test
     */
    public function it_gets_the_reference_entity_identifier()
    {
        Assert::assertEquals(
            'designer',
            ($this->getReferenceEntityIdentifierForRecordIdentifier)(RecordIdentifier::fromString('starck_designer_fingerprint'))->normalize()
        );
    }

    /**
     * @test
     */
    public function it_throws_if_the_record_does_not_exist()
    {
        $this->expectException(RecordNotFoundException::class);
        ($this->getReferenceEntityIdentifierForRecordIdentifier)(RecordIdentifier::fromString('wrong_code'));
    }

    private function loadReferenceEntityAndAttributes(): void
    {
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity')
            ->create(ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        ));

        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record')
            ->create(
                Record::create(
                    RecordIdentifier::fromString('starck_designer_fingerprint'),
                    ReferenceEntityIdentifier::fromString('designer'),
                    RecordCode::fromString('starck'),
                    ['fr_FR' => 'Philippe Starck'],
                    Image::createEmpty(),
                    ValueCollection::fromValues([])
                )
            );
    }
}
