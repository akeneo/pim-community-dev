<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRecordItemsForIdentifiersAndQuery;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRequiredValueKeyCollectionForChannelAndLocales;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryReferenceEntityRepository;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFindRecordItemsForIdentifiersAndQueryTest extends TestCase
{
    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var RecordIdentifier */
    private $starckIdentifier;

    /** @var RecordIdentifier */
    private $cocoIdentifier;

    /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocales */
    private $inMemoryRequiredQuery;
    /** @var InMemoryFindRecordItemsForIdentifiersAndQuery */
    private $query;

    public function setup()
    {
        $this->recordRepository = new InMemoryRecordRepository();
        $this->referenceEntityRepository = new InMemoryReferenceEntityRepository(new EventDispatcher());
        $this->attributeRepository = new InMemoryAttributeRepository(new EventDispatcher());
        $this->inMemoryRequiredQuery = new InMemoryFindRequiredValueKeyCollectionForChannelAndLocales(
            $this->attributeRepository
        );

        $this->query = new InMemoryFindRecordItemsForIdentifiersAndQuery(
            $this->recordRepository,
            $this->referenceEntityRepository,
            $this->inMemoryRequiredQuery
        );
    }

    /**
     * @test
     */
    public function it_return_an_empty_list_of_records_if_identifiers_are_not_matching()
    {
        $this->loadReferenceEntity();

        $query = RecordQuery::createFromNormalized([
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'filters' => [
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'designer'
                ]
            ],
            'page' => 1,
            'size' => 10,
        ]);

        $result = ($this->query)(['brand_kartell_fingerprint', 'brand_dyson_fingerprint'], $query);

        Assert::assertSame([], $result);
    }

    /**
     * @test
     */
    public function it_return_a_list_of_record_items_for_the_given_identifiers()
    {
        $this->loadReferenceEntity();
        $this->loadRecords();
        $this->loadAttributes();

        $query = RecordQuery::createFromNormalized([
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'filters' => [
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'designer'
                ]
            ],
            'page' => 1,
            'size' => 10,
        ]);

        $result = ($this->query)([$this->starckIdentifier, $this->cocoIdentifier], $query);

        $starkRecordItem = new RecordItem();
        $starkRecordItem->identifier = (string) $this->starckIdentifier;
        $starkRecordItem->referenceEntityIdentifier = 'designer';
        $starkRecordItem->code = 'starck';
        $starkRecordItem->labels = [
            'fr_FR' => 'Philippe Starck'
        ];
        $starkRecordItem->image = null;
        $starkRecordItem->values = [
            'label_designer_fingerprint_fr_FR' => [
                'attribute' => 'label_designer_fingerprint',
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'Philippe Starck'
            ],
            'name_designer_fingerprint' => [
                'attribute' => 'name_designer_fingerprint',
                'channel' => null,
                'locale' => null,
                'data' => 'The lord',
            ],
            'nickname_designer_fingerprint_fr_FR' => [
                'attribute' => 'nickname_designer_fingerprint',
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'Philou',
            ],
        ];
        $starkRecordItem->completeness = ['complete' => 1, 'required' => 2];

        $cocoRecordItem = new RecordItem();
        $cocoRecordItem->identifier = (string) $this->cocoIdentifier;
        $cocoRecordItem->referenceEntityIdentifier = 'designer';
        $cocoRecordItem->code = 'coco';
        $cocoRecordItem->labels = [
            'fr_FR' => 'Coco Chanel'
        ];
        $cocoRecordItem->image = null;
        $cocoRecordItem->values = [
            'label_designer_fingerprint_fr_FR' => [
                'attribute' => 'label_designer_fingerprint',
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'Coco Chanel'
            ]
        ];
        $cocoRecordItem->completeness = ['complete' => 0, 'required' => 2];
        ;

        $normalizedResults = array_map(function (RecordItem $item) {
            return $item->normalize();
        }, $result);

        Assert::assertSame(
            [$starkRecordItem->normalize(), $cocoRecordItem->normalize()],
            $normalizedResults
        );
    }

    /**
     * @test
     */
    public function it_return_a_partial_list_of_record_items_for_the_given_identifiers()
    {
        $this->loadReferenceEntity();
        $this->loadRecords();

        $query = RecordQuery::createFromNormalized([
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'filters' => [
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'designer'
                ]
            ],
            'page' => 1,
            'size' => 10,
        ]);

        $result = ($this->query)(['michel_sardou', $this->cocoIdentifier], $query);
        $starkRecordItem = new RecordItem();
        $starkRecordItem->identifier = (string) $this->starckIdentifier;
        $starkRecordItem->referenceEntityIdentifier = 'designer';
        $starkRecordItem->code = 'starck';
        $starkRecordItem->labels = [
            'fr_FR' => 'Philippe Starck'
        ];
        $starkRecordItem->image = null;
        $starkRecordItem->values = [];
        $starkRecordItem->completeness = ['complete' => 0, 'required' => 0];

        $cocoRecordItem = new RecordItem();
        $cocoRecordItem->identifier = (string) $this->cocoIdentifier;
        $cocoRecordItem->referenceEntityIdentifier = 'designer';
        $cocoRecordItem->code = 'coco';
        $cocoRecordItem->labels = [
            'fr_FR' => 'Coco Chanel'
        ];
        $cocoRecordItem->image = null;
        $cocoRecordItem->values = [
            'label_designer_fingerprint_fr_FR' => [
                'attribute' => 'label_designer_fingerprint',
                'channel' => null,
                'locale' => 'fr_FR',
                'data' => 'Coco Chanel'
            ]
        ];
        $cocoRecordItem->completeness = ['complete' => 0, 'required' => 0];

        $normalizedResults = array_map(function (RecordItem $item) {
            return $item->normalize();
        }, $result);

        Assert::assertSame(
            [$cocoRecordItem->normalize()],
            $normalizedResults
        );
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntity = ReferenceEntity::createWithAttributes(
            ReferenceEntityIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            AttributeAsLabelReference::createFromNormalized('label_designer_fingerprint'),
            AttributeAsImageReference::createFromNormalized('image_designer_fingerprint')
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }

    private function loadRecords(): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $starkCode = RecordCode::fromString('starck');
        $this->starckIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $starkCode);
        $this->recordRepository->create(
            Record::create(
                $this->starckIdentifier,
                $referenceEntityIdentifier,
                $starkCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Philippe Starck')
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('The lord')
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('nickname_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Philou')
                    ),
                ])
            )
        );
        $cocoCode = RecordCode::fromString('coco');
        $this->cocoIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $cocoCode);
        $this->recordRepository->create(
            Record::create(
                $this->cocoIdentifier,
                $referenceEntityIdentifier,
                $cocoCode,
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('label_designer_fingerprint'),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Coco Chanel')
                    ),
                ])
            )
        );
    }

    private function loadAttributes(): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $this->inMemoryRequiredQuery->setActivatedChannels(['ecommerce']);
        $this->inMemoryRequiredQuery->setActivatedLocales(['en_US', 'fr_FR']);

        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'name', 'fingerprint'),
                $referenceEntityIdentifier,
                AttributeCode::fromString('name'),
                LabelCollection::fromArray(['fr_FR' => 'Nom']),
                AttributeOrder::fromInteger(0),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(false),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );

        $this->attributeRepository->create(
            TextAttribute::createText(
                AttributeIdentifier::create('designer', 'nickname', 'fingerprint'),
                $referenceEntityIdentifier,
                AttributeCode::fromString('nickname'),
                LabelCollection::fromArray(['fr_FR' => 'Nom']),
                AttributeOrder::fromInteger(1),
                AttributeIsRequired::fromBoolean(true),
                AttributeValuePerChannel::fromBoolean(false),
                AttributeValuePerLocale::fromBoolean(true),
                AttributeMaxLength::fromInteger(25),
                AttributeValidationRule::none(),
                AttributeRegularExpression::createEmpty()
            )
        );
    }
}
