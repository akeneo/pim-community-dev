<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValueInterface;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ReferenceEntityValueStringifier extends AbstractValueStringifier implements ValueStringifierInterface
{
    private const LABEL_LOCALE_KEY = 'label_locale';
    private const SEPARATOR = ', ';

    /** @var FindRecordDetailsInterface */
    private $findRecordDetails;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(
        FindRecordDetailsInterface $findRecordDetails,
        GetAttributes $getAttributes,
        array $attributeTypes
    ) {
        parent::__construct($attributeTypes);
        $this->findRecordDetails = $findRecordDetails;
        $this->getAttributes = $getAttributes;
    }

    public function stringify(ValueInterface $value, array $options = []): string
    {
        if (!$value instanceof ReferenceEntityValueInterface
            && !$value instanceof ReferenceEntityCollectionValueInterface
        ) {
            throw new \InvalidArgumentException(sprintf(
                'The value must an instance of %s, or %s, % given',
                ReferenceEntityValueInterface::class,
                ReferenceEntityCollectionValueInterface::class,
                get_class($value)
            ));
        }

        if (null === $value->getData() || '' === $value->getData() || [] === $value->getData()) {
            return '';
        }

        $recordCodes = is_array($value->getData()) ? $value->getData() : [$value->getData()];

        $labelLocale = $options[static::LABEL_LOCALE_KEY] ?? null;
        $attribute = $this->getAttributes->forCode($value->getAttributeCode());
        if (null === $attribute) {
            return '';
        }

        $referenceDataName = $attribute->properties()['reference_data_name'] ?? null;
        if (!is_string($referenceDataName)) {
            return '';
        }

        $strings = [];
        /** @var null|RecordCode $recordCode */
        foreach ($recordCodes as $recordCode) {
            $string = $this->stringifyOneRecord($recordCode, $labelLocale, $referenceDataName);
            if ('' !== $string) {
                $strings[] = $string;
            }
        }

        return implode(static::SEPARATOR, $strings);
    }

    private function stringifyOneRecord(
        RecordCode $recordCode,
        ?string $labelLocale,
        string $referenceEntityIdentifier
    ): string {
        $stringRecordCode = $recordCode->normalize();

        $recordDetails = $this->findRecordDetails->find(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            $recordCode
        );
        if (null === $recordDetails) {
            return '';
        }

        return null === $labelLocale
            ? $stringRecordCode
            : $recordDetails->labels->getLabel($labelLocale) ?? $stringRecordCode
        ;
    }
}
