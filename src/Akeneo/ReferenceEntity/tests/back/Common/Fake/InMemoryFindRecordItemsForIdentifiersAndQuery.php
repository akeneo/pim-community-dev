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
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordItemsForIdentifiersAndQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

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

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredValueKeyCollectionForChannelAndLocales
    ) {
        $this->recordRepository = $recordRepository;
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->findRequiredValueKeyCollectionForChannelAndLocales = $findRequiredValueKeyCollectionForChannelAndLocales;

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
        $channelIdentifier = ChannelIdentifier::fromCode($query->getChannel());
        $localeIdentifiers = LocaleIdentifierCollection::fromNormalized([$query->getLocale()]);
        $attributeAsLabel = $referenceEntity->getAttributeAsLabelReference();
        $attributeAsImage = $referenceEntity->getAttributeAsImageReference();


        /** @var ValueKeyCollection $requiredValueKeyCollection */
        $requiredValueKeyCollection = ($this->findRequiredValueKeyCollectionForChannelAndLocales)(
            $referenceEntityIdentifier,
            $channelIdentifier,
            $localeIdentifiers
        );
        $requiredValueKeys = $requiredValueKeyCollection->normalize();

        $array_values = array_values(array_filter(array_map(function (string $identifier) use (
            $requiredValueKeys,
            $attributeAsLabel,
            $attributeAsImage
        ) {
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

            $labels = $this->getLabelsFromValues($valueCollection, $attributeAsLabel->normalize());

            $imageValue = $this->getImage($valueCollection, $attributeAsImage->normalize());
            $image = null;
            if (!empty($imageValue)) {
                $file = new FileInfo();
                $file->setKey($imageValue['filePath']);
                $file->setOriginalFilename($imageValue['originalFilename']);
                $image = Image::fromFileInfo($file);
            }

            $recordItem = new RecordItem();
            $recordItem->identifier = (string) $record->getIdentifier();
            $recordItem->referenceEntityIdentifier = (string) $record->getReferenceEntityIdentifier();
            $recordItem->code = (string) $record->getCode();
            $recordItem->labels = $labels;
            $recordItem->image = $image;
            $recordItem->values = $record->getValues()->normalize();
            $recordItem->completeness = $completeness;

            return $recordItem;
        }, $identifiers)));

        return $array_values;
    }

    private function getLabelsFromValues(array $valueCollection, string $attributeAsLabel): array
    {
        return array_reduce(
            $valueCollection,
            function (array $labels, array $value) use ($attributeAsLabel) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $localeCode = $value['locale'];
                    $label = (string) $value['data'];
                    $labels[$localeCode] = $label;
                }

                return $labels;
            },
            []
        );
    }

    private function getImage(array $valueCollection, string $attributeAsImage)
    {
        return array_filter(
            $valueCollection,
            function (array $value) use ($attributeAsImage) {
                return $value['attribute'] === $attributeAsImage;
            }
        );
    }
}
