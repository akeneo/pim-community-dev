<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Write\Value\AbstractValueFactory;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValue;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates reference entity product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ReferenceEntityValueFactory extends AbstractValueFactory
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    public function __construct(RecordRepositoryInterface $recordRepository)
    {
        parent::__construct(ReferenceEntityValue::class, ReferenceEntityType::REFERENCE_ENTITY);

        $this->recordRepository = $recordRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
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

        return $this->getRecordCode($attribute, $data, $ignoreUnknownData);
    }

    /**
     * Gets the Record code (or null) for the given $code
     */
    private function getRecordCode(AttributeInterface $attribute, string $code, bool $ignoreUnknownData): ?RecordCode
    {
        if (null === $code) {
            return null;
        }

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

        try {
            $record = $this->recordRepository->getByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
        } catch (RecordNotFoundException $exception) {
            if ($ignoreUnknownData) {
                // The record has been removed, we don't crash the app but set record to null.
                return null;
            }
            throw $exception;
        }

        return $record->getCode();
    }
}
