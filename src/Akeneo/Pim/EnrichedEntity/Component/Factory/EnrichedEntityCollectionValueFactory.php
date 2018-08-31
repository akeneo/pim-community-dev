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

namespace Akeneo\Pim\EnrichedEntity\Component\Factory;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Pim\EnrichedEntity\Component\AttributeType\EnrichedEntityCollectionType;
use Akeneo\Pim\EnrichedEntity\Component\Value\EnrichedEntityCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates enriched entity product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ProductValueFactory.
 *
 * @author    Julien Sanchez (julien@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityCollectionValueFactory implements ValueFactoryInterface
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /**
     * @param RecordRepositoryInterface $recordRepository
     */
    public function __construct(RecordRepositoryInterface $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data): ValueInterface
    {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = [];
        }

        $value = new EnrichedEntityCollectionValue(
            $attribute,
            $channelCode,
            $localeCode,
            $this->getRecordCollection($attribute, $data)
        );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType): bool
    {
        return $attributeType === EnrichedEntityCollectionType::ENRICHED_ENTITY_COLLECTION;
    }

    /**
     * Checks if data is valid.
     *
     * @throws InvalidPropertyTypeException
     */
    private function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data || [] === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $attribute->getCode(),
                    sprintf('array key "%s" expects a string as value, "%s" given', $key, gettype($value)),
                    static::class,
                    $data
                );
            }
        }
    }

    /**
     * Gets a collection of reference data from an array of codes.
     *
     * @throws InvalidPropertyTypeException
     */
    private function getRecordCollection(AttributeInterface $attribute, array $recordCodes): array
    {
        $collection = [];

        foreach ($recordCodes as $code) {
            $enrichedEntityCode = $attribute->getReferenceDataName();
            $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityCode);
            $recordCode = RecordCode::fromString($code);
            $recordIdentifier = $this->recordRepository->nextIdentifier($enrichedEntityIdentifier, $recordCode);

            $record = $this->recordRepository->getByIdentifier($recordIdentifier);

            if (null === $record) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $attribute->getCode(),
                    'record code',
                    sprintf(
                        'The code of the enriched entity "%s" does not exist',
                        (string) $enrichedEntityIdentifier
                    ),
                    static::class,
                    (string) $recordIdentifier
                );
            }

            if (!in_array($record, $collection, true)) {
                $collection[] = $record;
            }
        }

        return $collection;
    }
}
