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

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordItemsForIdentifiersAndQuery implements FindRecordItemsForIdentifiersAndQueryInterface
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocale */
    private $findRequiredValueKeyCollectionForChannelAndLocale;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        InMemoryFindRequiredValueKeyCollectionForChannelAndLocale $findRequiredValueKeyCollectionForChannelAndLocale
    ) {
        $this->recordRepository = $recordRepository;
        $this->findRequiredValueKeyCollectionForChannelAndLocale = $findRequiredValueKeyCollectionForChannelAndLocale;

        $this->findRequiredValueKeyCollectionForChannelAndLocale->setActivatedLocales(['en_US']);
        $this->findRequiredValueKeyCollectionForChannelAndLocale->setActivatedChannels(['ecommerce']);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $identifiers, RecordQuery $query): array
    {
        $referenceEntityFilter = $query->getFilter('reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityFilter['value']);
        $channelIdentifier = ChannelIdentifier::fromCode($query->getChannel());
        $localeIdentifier = LocaleIdentifier::fromCode($query->getLocale());

        /** @var ValueKeyCollection $requiredValueKeyCollection */
        $requiredValueKeyCollection = ($this->findRequiredValueKeyCollectionForChannelAndLocale)(
            $referenceEntityIdentifier,
            $channelIdentifier,
            $localeIdentifier
        );
        $requiredValueKeys = $requiredValueKeyCollection->normalize();

        return array_values(array_filter(array_map(function (string $identifier) use ($requiredValueKeys) {
            try {
                $record = $this->recordRepository->getByIdentifier(RecordIdentifier::fromString($identifier));
            } catch (RecordNotFoundException $exception) {
                return false;
            }

            $valueCollection = $record->getValues()->normalize();
            $completeness = ['complete' => 0, 'required' => 0];
            if (count($requiredValueKeys) > 0) {
                $existingValueKeys = array_keys($valueCollection);
                $completeness['complete'] = count(array_intersect($requiredValueKeys, $existingValueKeys));
                $completeness['required'] = count($requiredValueKeys);
            }

            $recordItem = new RecordItem();
            $recordItem->identifier = (string) $record->getIdentifier();
            $recordItem->referenceEntityIdentifier = (string) $record->getReferenceEntityIdentifier();
            $recordItem->code = (string) $record->getCode();
            $recordItem->labels = $record->normalize()['labels'];
            $recordItem->image = $record->getImage()->normalize();
            $recordItem->values = $record->getValues()->normalize();
            $recordItem->completeness = $completeness;

            return $recordItem;
        }, $identifiers)));
    }
}
