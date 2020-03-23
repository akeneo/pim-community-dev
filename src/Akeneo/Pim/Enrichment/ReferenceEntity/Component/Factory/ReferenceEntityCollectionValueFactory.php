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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\AbstractValueFactory;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates reference entity product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Julien Sanchez (julien@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityCollectionValueFactory extends AbstractValueFactory
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /**
     * @param RecordRepositoryInterface $recordRepository
     */
    public function __construct(RecordRepositoryInterface $recordRepository)
    {
        parent::__construct(
            ReferenceEntityCollectionValue::class,
            ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION
        );

        $this->recordRepository = $recordRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
    {
        if (null === $data) {
            $data = [];
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

        return $this->getRecordCodeCollection($attribute, $data, $ignoreUnknownData);
    }

    /**
     * Gets a collection of record code object from an array of string codes
     *
     * @throws InvalidPropertyTypeException
     */
    protected function getRecordCodeCollection(AttributeInterface $attribute, array $recordCodes, bool $ignoreUnknownData): array
    {
        $collection = [];

        foreach ($recordCodes as $code) {
            $referenceEntityIdentifier = $attribute->getReferenceDataName();
            try {
                $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
            } catch (\InvalidArgumentException $exception) {
                if ($ignoreUnknownData) {
                    return null;
                }
                throw InvalidPropertyException::expected($exception->getMessage(), ReferenceEntityIdentifier::class);
            }

            try {
                $recordCode = RecordCode::fromString($code);
            } catch (\InvalidArgumentException $exception) {
                if ($ignoreUnknownData) {
                    return null;
                }
                throw InvalidPropertyException::expected($exception->getMessage(), RecordCode::class);
            }

            if (!in_array($recordCode, $collection, true)) {
                $collection[] = $recordCode;
            }
        }

        return $collection;
    }
}
