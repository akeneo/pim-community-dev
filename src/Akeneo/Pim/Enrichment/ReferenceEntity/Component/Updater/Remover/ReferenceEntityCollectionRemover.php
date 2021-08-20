<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Remover;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\AbstractAttributeRemover;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Webmozart\Assert\Assert;

final class ReferenceEntityCollectionRemover extends AbstractAttributeRemover
{
    private EntityWithValuesBuilderInterface $entityWithValuesBuilder;

    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        parent::__construct($attrValidatorHelper);
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
    }

    public function removeAttributeData(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ): void {
        if (!\is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($attribute->getCode(), self::class, $data);
        }
        try {
            Assert::allStringNotEmpty($data);
        } catch (\InvalidArgumentException $e) {
            throw InvalidPropertyTypeException::arrayOfStringsExpected(
                $attribute->getCode(),
                self::class,
                $data
            );
        }

        $options = $this->resolver->resolve($options);
        $value = $entityWithValues->getValue($attribute->getCode(), $options['locale'], $options['scope']);
        if (!$value instanceof ReferenceEntityCollectionValueInterface) {
            return;
        }

        $updatedData = \array_diff(
            \array_map(
                fn (RecordCode $recordCode): string => $recordCode->__toString(),
                $value->getData()
            ),
            $data
        );

        $this->entityWithValuesBuilder->addOrReplaceValue(
            $entityWithValues,
            $attribute,
            $options['locale'],
            $options['scope'],
            \array_values($updatedData)
        );
    }

    public function supportsAttribute(AttributeInterface $attribute): bool
    {
        return AttributeTypes::REFERENCE_ENTITY_COLLECTION === $attribute->getType();
    }
}
