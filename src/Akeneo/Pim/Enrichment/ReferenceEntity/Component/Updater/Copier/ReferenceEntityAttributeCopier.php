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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AbstractAttributeCopier;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;

class ReferenceEntityAttributeCopier extends AbstractAttributeCopier
{
    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedFromTypes,
        array $supportedToTypes
    ) {
        parent::__construct($entityWithValuesBuilder, $attrValidatorHelper);

        $this->supportedFromTypes = $supportedFromTypes;
        $this->supportedToTypes = $supportedToTypes;
    }

    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        $supportsFrom = in_array($fromAttribute->getType(), $this->supportedFromTypes);
        $supportsTo = in_array($toAttribute->getType(), $this->supportedToTypes);
        $sameReferenceEntity = ($fromAttribute->getReferenceDataName() === $toAttribute->getReferenceDataName());

        return $supportsFrom && $supportsTo && $sameReferenceEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function copyAttributeData(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);
        $fromLocale = $options['from_locale'];
        $toLocale = $options['to_locale'];
        $fromScope = $options['from_scope'];
        $toScope = $options['to_scope'];

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope);
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope);

        $fromValue = $fromEntityWithValues->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        $data = (null !== $fromValue && null !== $fromValue->getData()) ? $fromValue->getData() : null;
        if ($data instanceof RecordCode) {
            $data = $data->normalize();
        } elseif (is_array($data)) {
            $data = array_map(function (RecordCode $recordCode): string {
                return $recordCode->normalize();
            }, $data);
        }

        $this->entityWithValuesBuilder->addOrReplaceValue(
            $toEntityWithValues,
            $toAttribute,
            $toLocale,
            $toScope,
            $data
        );
    }
}
