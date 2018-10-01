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

namespace Akeneo\Pim\ReferenceEntity\Component\Factory;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Pim\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates enriched entity product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Julien Sanchez (julien@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityCollectionValueFactory implements ValueFactoryInterface
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

        $value = new ReferenceEntityCollectionValue(
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
        return $attributeType === ReferenceEntityCollectionType::ENRICHED_ENTITY_COLLECTION;
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
            $referenceEntityIdentifier = $attribute->getReferenceDataName();
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
            $recordCode = RecordCode::fromString($code);

            try {
                $record = $this->recordRepository->getByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
            } catch (RecordNotFoundException $e) {
                // The record has been removed, we can go on and continue to load the rest of the records.

                continue;
            }

            if (!in_array($record, $collection, true)) {
                $collection[] = $record;
            }
        }

        return $collection;
    }
}
