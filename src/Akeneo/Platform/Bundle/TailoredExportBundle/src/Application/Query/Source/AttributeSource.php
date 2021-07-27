<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Query\Source;

use Akeneo\Platform\TailoredExport\Application\Query\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Operation\OperationInterface;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;

class AttributeSource implements SourceInterface
{
    public const TYPE = 'attribute';

    private string $attributeType;
    private string $code;
    private ?string $channelReference;
    private ?string $localeReference;
    private OperationCollection $operations;
    private SelectionInterface $selection;

    public function __construct(
        string $attributeType,
        string $code,
        ?string $channelReference,
        ?string $localeReference,
        OperationCollection $operations,
        SelectionInterface $selection
    ) {
        $this->attributeType = $attributeType;
        $this->code = $code;
        $this->channelReference = $channelReference;
        $this->localeReference = $localeReference;
        $this->operations = $operations;
        $this->selection = $selection;
    }

    public function getAttributeType(): string
    {
        return $this->attributeType;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getChannel(): ?string
    {
        return $this->channelReference;
    }

    public function getLocale(): ?string
    {
        return $this->localeReference;
    }

    public function getOperationCollection(): OperationCollection
    {
        return $this->operations;
    }

    public function getSelection(): SelectionInterface
    {
        return $this->selection;
    }

    public function getAllLocaleCodes(): array
    {
        $sourceLocales = null === $this->getLocale() ? [] : [$this->getLocale()];
        $operationLocales = array_reduce(
            iterator_to_array($this->getOperationCollection()),
            function (array $result, OperationInterface $operation) {
                return array_merge($result, $operation->getAllLocaleCodes());
            },
            []
        );
        $selectionLocales = $this->selection->getAllLocaleCodes();

        return $sourceLocales + $operationLocales + $selectionLocales;
    }

    public function getAllAttributeCodes(): array
    {
        $sourceAttributes = [$this->getCode()];
        $operationAttributes = array_reduce(
            iterator_to_array($this->getOperationCollection()),
            function (array $result, OperationInterface $operation) {
                return array_merge($result, $operation->getAllAttributeCodes());
            },
            []
        );
        $selectionAttributes = $this->selection->getAllAttributeCodes();

        return $sourceAttributes + $operationAttributes + $selectionAttributes;
    }
}
