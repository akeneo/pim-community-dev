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

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates reference entity product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ReferenceEntityValueFactory implements ValueFactoryInterface
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
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data, bool $ignoreUnknownData = false): ValueInterface
    {
        $this->checkData($attribute, $data);

        $value = new ReferenceEntityValue(
            $attribute,
            $channelCode,
            $localeCode,
            $this->getRecord($attribute, $data)
        );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType): bool
    {
        return $attributeType === ReferenceEntityType::REFERENCE_ENTITY;
    }

    /**
     * Checks if data is valid.
     *
     * @throws InvalidPropertyTypeException
     */
    private function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }
    }

    /**
     * Gets the Record (or null) for the given $code
     */
    private function getRecord(AttributeInterface $attribute, $code): ?Record
    {
        if (null === $code) {
            return null;
        }

        $referenceEntityIdentifier = $attribute->getReferenceDataName();
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        $recordCode = RecordCode::fromString($code);

        try {
            $record = $this->recordRepository->getByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
        } catch (RecordNotFoundException $e) {
            // The record has been removed, we don't crash the app but set record to null.
            $record = null;
        }

        return $record;
    }
}
