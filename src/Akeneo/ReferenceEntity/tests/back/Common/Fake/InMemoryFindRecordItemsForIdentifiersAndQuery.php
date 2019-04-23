<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\BulkRecordItemHydrator;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordItemsForIdentifiersAndQuery implements FindRecordItemsForIdentifiersAndQueryInterface
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var ReferenceEntityRepositoryInterface  */
    private $referenceEntityRepository;

    /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocales */
    private $findRequiredValueKeyCollectionForChannelAndLocales;

    /** @var BulkRecordItemHydrator */
    private $bulkRecordItemHydrator;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredValueKeyCollectionForChannelAndLocales,
        BulkRecordItemHydrator $bulkRecordItemHydrator
    ) {
        $this->recordRepository = $recordRepository;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->findRequiredValueKeyCollectionForChannelAndLocales = $findRequiredValueKeyCollectionForChannelAndLocales;
        $this->bulkRecordItemHydrator = $bulkRecordItemHydrator;

        $this->findRequiredValueKeyCollectionForChannelAndLocales->setActivatedLocales(['en_US']);
        $this->findRequiredValueKeyCollectionForChannelAndLocales->setActivatedChannels(['ecommerce']);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $identifiers, RecordQuery $query): array
    {
        $referenceEntityFilter = $query->getFilter('reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityFilter['value']);
        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        $attributeAsLabel = $referenceEntity->getAttributeAsLabelReference();
        $attributeAsImage = $referenceEntity->getAttributeAsImageReference();

        $query = RecordQuery::createFromNormalized([
           'locale' => $query->getChannel(),
           'channel' => $query->getLocale(),
           'size' => 20,
           'page' => 0,
           'filters' => [
               [
                   'field' => 'reference_entity',
                   'operator' => '=',
                   'value' => $referenceEntityIdentifier->normalize(),
                   'context' => []
               ]
           ]
        ]);

        $normalizedRecordItems = array_values(array_filter(array_map(function (string $identifier) use (
            $attributeAsLabel,
            $attributeAsImage
        ) {
            try {
                $record = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($identifier));
            } catch (RecordNotFoundException $exception) {
                return false;
            }

            $normalizedRecordItem = [
                'identifier' => (string) $record->getIdentifier(),
                'reference_entity_identifier' => (string) $record->getReferenceEntityIdentifier(),
                'code' => (string) $record->getCode(),
                'value_collection' => json_encode($record->getValues()->normalize()),
                'attribute_as_image' => $attributeAsImage->normalize(),
                'attribute_as_label' => $attributeAsLabel->normalize()
            ];

            return $normalizedRecordItem;
        }, $identifiers)));

        $recordItems = $this->bulkRecordItemHydrator->hydrateAll($normalizedRecordItems, $query);

        return $recordItems;
    }
}
