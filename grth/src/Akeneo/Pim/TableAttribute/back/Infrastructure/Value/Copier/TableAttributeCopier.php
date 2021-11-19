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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AbstractAttributeCopier;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;

final class TableAttributeCopier extends AbstractAttributeCopier
{
    public function copyAttributeData(
        EntityWithValuesInterface $fromEntityWithValues,
        EntityWithValuesInterface $toEntityWithValues,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        array $options = []
    ): void {
        $options = $this->resolver->resolve($options);
        $fromLocale = $options['from_locale'];
        $toLocale = $options['to_locale'];
        $fromScope = $options['from_scope'];
        $toScope = $options['to_scope'];

        $this->checkLocaleAndScope($fromAttribute, $fromLocale, $fromScope);
        $this->checkLocaleAndScope($toAttribute, $toLocale, $toScope);

        $fromValue = $fromEntityWithValues->getValue($fromAttribute->getCode(), $fromLocale, $fromScope);
        $data = $fromValue !== null ? $fromValue->getData() : null;
        if ($data instanceof Table) {
            $data = $data->normalize();
        }

        $this->entityWithValuesBuilder->addOrReplaceValue(
            $toEntityWithValues,
            $toAttribute,
            $toLocale,
            $toScope,
            $data
        );
    }

    public function supportsAttributes(AttributeInterface $fromAttribute, AttributeInterface $toAttribute): bool
    {
        return AttributeTypes::TABLE === $fromAttribute->getType() && AttributeTypes::TABLE === $toAttribute->getType()
            && $fromAttribute->getCode() === $toAttribute->getCode();
    }
}
